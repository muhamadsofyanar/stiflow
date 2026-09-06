<?php

namespace Tests\Feature;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StifinOperationOutcome;
use App\Enums\UserRole;
use App\Integrations\Stifin\StifinApiClient;
use App\Integrations\Stifin\StifinOperationResult;
use App\Models\Order;
use App\Models\OutboxEvent;
use App\Models\PaymentAttempt;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\StifinOperation;
use App\Models\User;
use App\Services\Payment\PaymentVerificationService;
use App\Services\Voucher\VoucherFulfillmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class VoucherIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_running_fulfillment_twice_creates_single_stifin_operation_success(): void
    {
        [$promotor, $admin, $order, $proof] = $this->seedBaseline();
        app(PaymentVerificationService::class)->approvePayment($proof, $admin, 'ok');

        $event = OutboxEvent::query()
            ->where('aggregate_type', Order::class)
            ->where('aggregate_id', $order->id)
            ->firstOrFail();

        $success = new StifinOperationResult(
            isSuccess: true, httpCode: 200, rawBody: '{"ok":true}', parsedData: ['ok' => true],
        );

        $mockClient = Mockery::mock(StifinApiClient::class);
        $mockClient->shouldReceive('getVoucherBalance')->atLeast()->once()->andReturn(
            ['paid' => 5, 'free' => 0],
            ['paid' => 6, 'free' => 0],
            ['paid' => 6, 'free' => 0],
        );
        $mockClient->shouldReceive('addVoucher')->once()->andReturn($success);
        $mockClient->shouldReceive('getUserIdIdentifier')->zeroOrMoreTimes()->andReturn('TEST-ID');
        $this->app->instance(StifinApiClient::class, $mockClient);

        app(VoucherFulfillmentService::class)->processOutbox($event);
        app(VoucherFulfillmentService::class)->processOutbox($event);

        $order->refresh();
        $this->assertEquals(OrderStatus::Completed, $order->status);

        $opsCount = StifinOperation::query()
            ->where('operation_type', 'add_voucher')
            ->where('outcome', StifinOperationOutcome::Success)
            ->count();
        $this->assertSame(1, $opsCount);

        $fulfillment = $order->items->first()->fulfillment;
        $this->assertEquals(FulfillmentStatus::Success, $fulfillment->status);
    }

    private function seedBaseline(): array
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'P-IDEM', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'pidem',
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        \App\Models\BranchSetting::query()->create([
            'branch_code' => 'BR-ID', 'brand_name' => 'T', 'bank_name' => 'BCA', 'bank_account' => '1',
            'bank_account_name' => 'T', 'locale' => 'id_ID', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
        ]);
        $product = Product::query()->create([
            'type' => 'voucher', 'name' => 'VT', 'slug' => 'vt-idem',
            'status' => 'active', 'visibility' => 'login_only', 'price' => 100000,
        ]);
        $product->voucherConfig()->create([
            'unit_price' => 100000, 'min_qty' => 1, 'max_qty' => 100, 'presets_json' => [1],
        ]);
        $order = Order::query()->create([
            'number' => 'INV-IDEM-' . time(), 'user_id' => $promotor->id,
            'promotor_code_snapshot' => 'P-IDEM',
            'currency' => 'IDR', 'subtotal' => 100000, 'discount' => 0, 'total' => 100000,
            'status' => OrderStatus::PaymentSubmitted, 'expires_at' => now()->addDay(),
        ]);
        $order->items()->create([
            'product_snapshot_json' => ['name' => 'VT', 'promotor_code_target' => 'P-IDEM'],
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
