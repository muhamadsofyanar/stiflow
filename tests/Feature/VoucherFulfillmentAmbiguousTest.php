<?php

namespace Tests\Feature;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PromoterVerificationStatus;
use App\Enums\ReconciliationStatus;
use App\Enums\StifinOperationOutcome;
use App\Enums\UserRole;
use App\Integrations\Stifin\StifinApiClient;
use App\Integrations\Stifin\StifinOperationResult;
use App\Jobs\ProcessVoucherFulfillmentJob;
use App\Models\BranchSetting;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\OutboxEvent;
use App\Models\PaymentAttempt;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\ReconciliationCase;
use App\Models\User;
use App\Services\Payment\PaymentVerificationService;
use App\Services\Voucher\VoucherFulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class VoucherFulfillmentAmbiguousTest extends TestCase
{
    use RefreshDatabase;

    public function test_balance_read_failure_never_attempts_voucher_post(): void
    {
        [, $admin, $order, $proof] = $this->seedBaseline();
        app(PaymentVerificationService::class)->approvePayment($proof, $admin, 'ok');
        $event = OutboxEvent::query()->where('aggregate_id', $order->id)->firstOrFail();

        $mockClient = \Mockery::mock(StifinApiClient::class);
        $mockClient->shouldReceive('getVoucherBalance')
            ->once()
            ->andThrow(new RuntimeException('Saldo voucher STIFIN tidak dapat dibaca.'));
        $mockClient->shouldNotReceive('addVoucher');
        $mockClient->shouldReceive('getUserIdIdentifier')->zeroOrMoreTimes()->andReturn('TEST-USER');
        $this->app->instance(StifinApiClient::class, $mockClient);

        app(VoucherFulfillmentService::class)->processOutbox($event);

        $this->assertSame(OrderStatus::NeedsReview, $order->fresh()->status);
        $this->assertSame(FulfillmentStatus::Failed, $order->items->first()->fulfillment->fresh()->status);
        $this->assertSame('failed', $event->fresh()->worker_result);
        $this->assertNotNull($event->fresh()->processed_at);
    }

    public function test_post_timeout_marks_needs_review_no_retry(): void
    {
        [$promotor, $admin, $order, $proof] = $this->seedBaseline();
        app(PaymentVerificationService::class)->approvePayment($proof, $admin, 'ok');

        $event = OutboxEvent::query()
            ->where('aggregate_type', Order::class)
            ->where('aggregate_id', $order->id)
            ->firstOrFail();

        $ambiguousResult = new StifinOperationResult(
            isSuccess: false, httpCode: null, rawBody: '', parsedData: null,
            isAmbiguousTimeout: true, errorMessage: 'cURL error 28: Operation timed out'
        );

        $mockClient = \Mockery::mock(StifinApiClient::class);
        $mockClient->shouldReceive('getVoucherBalance')->zeroOrMoreTimes()->andReturn(['paid' => 10, 'free' => 0]);
        $mockClient->shouldReceive('addVoucher')->once()->andReturn($ambiguousResult);
        $mockClient->shouldReceive('getUserIdIdentifier')->zeroOrMoreTimes()->andReturn('TEST-USER');
        $this->app->instance(StifinApiClient::class, $mockClient);

        app(VoucherFulfillmentService::class)->processOutbox($event);
        app(VoucherFulfillmentService::class)->processOutbox($event->fresh());

        $order->refresh();
        $this->assertEquals(OrderStatus::NeedsReview, $order->status);

        $fulfillment = $order->items->first()->fulfillment;
        $this->assertNotNull($fulfillment);
        $this->assertEquals(FulfillmentStatus::NeedsReview, $fulfillment->status);

        $ops = $fulfillment->stifinOperations()->get();
        $this->assertCount(1, $ops);
        $this->assertEquals(StifinOperationOutcome::AmbiguousTimeout, $ops->first()->outcome);

        $cases = ReconciliationCase::query()
            ->where('subject_type', Fulfillment::class)
            ->where('subject_id', $fulfillment->id)
            ->get();
        $this->assertCount(1, $cases);
        $this->assertEquals(ReconciliationStatus::Open, $cases->first()->status);

        $retriesCreated = DB::table('jobs')
            ->where('payload', 'like', '%ProcessVoucherFulfillmentJob%')
            ->count();
        $this->assertSame(0, $retriesCreated);

        $event->refresh();
        $this->assertNotNull($event->processed_at);
        $this->assertSame('needs_review', $event->worker_result);

        Bus::fake();
        $this->artisan('outbox:dispatch')->assertSuccessful();
        Bus::assertNotDispatched(ProcessVoucherFulfillmentJob::class);
    }

    private function seedBaseline(): array
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'P-AMB',
            'verification_status' => PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'pambig',
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        BranchSetting::query()->create([
            'branch_code' => 'BR-AMB', 'brand_name' => 'T', 'bank_name' => 'BCA', 'bank_account' => '1',
            'bank_account_name' => 'T', 'locale' => 'id_ID', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
        ]);
        $product = Product::query()->create([
            'type' => 'voucher', 'name' => 'VT', 'slug' => 'vt-amb',
            'status' => 'active', 'visibility' => 'login_only', 'price' => 100000,
        ]);
        $product->voucherConfig()->create([
            'unit_price' => 100000, 'min_qty' => 1, 'max_qty' => 100, 'presets_json' => [1],
        ]);

        $order = Order::query()->create([
            'number' => 'INV-AMB-'.time(), 'user_id' => $promotor->id,
            'promotor_code_snapshot' => 'P-AMB',
            'currency' => 'IDR', 'subtotal' => 100000, 'discount' => 0, 'total' => 100000,
            'status' => OrderStatus::PaymentSubmitted, 'expires_at' => now()->addDay(),
        ]);
        $order->items()->create([
            'product_snapshot_json' => ['name' => 'VT', 'promotor_code_target' => 'P-AMB'],
            'quantity' => 1, 'unit_price' => 100000, 'discount' => 0, 'total' => 100000,
            'fulfillment_type' => 'voucher',
        ]);
        $attempt = PaymentAttempt::query()->create([
            'order_id' => $order->id, 'method' => 'manual_transfer', 'provider' => 'internal_manual',
            'amount' => 100000, 'currency' => 'IDR', 'status' => PaymentStatus::Submitted,
        ]);
        $proof = PaymentProof::query()->create([
            'payment_attempt_id' => $attempt->id,
            'file_path' => 'payment_proofs/t.jpg', 'original_filename' => 't.jpg',
            'mime_type' => 'image/jpeg', 'file_size' => 1000, 'review_status' => 'pending',
            'transfer_amount' => 100000, 'transfer_time' => now(),
        ]);

        return [$promotor, $admin, $order, $proof];
    }
}
