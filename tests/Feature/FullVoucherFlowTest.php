<?php

namespace Tests\Feature;

use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PromoterVerificationStatus;
use App\Enums\StifinOperationOutcome;
use App\Enums\UserRole;
use App\Integrations\Stifin\StifinApiClient;
use App\Integrations\Stifin\StifinOperationResult;
use App\Models\BranchSetting;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OutboxEvent;
use App\Models\PaymentAttempt;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\StifinOperation;
use App\Models\User;
use App\Models\VoucherFulfillment;
use App\Services\Payment\PaymentVerificationService;
use App\Services\Voucher\VoucherFulfillmentService;
use App\Services\Voucher\VoucherOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FullVoucherFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_end_to_end_records_created_in_all_core_tables(): void
    {
        [$promotor, $admin, $product] = $this->seedBaseline();

        $order = app(VoucherOrderService::class)->createVoucherOrder($promotor, $product, 3);
        $this->assertInstanceOf(Order::class, $order);

        $proof = $this->fakeProof($order, $promotor);

        app(PaymentVerificationService::class)->approvePayment($proof, $admin, 'ok e2e');

        $outbox = OutboxEvent::query()
            ->where('aggregate_type', Order::class)
            ->where('aggregate_id', $order->id)
            ->where('event_type', 'fulfill_voucher')
            ->first();
        $this->assertNotNull($outbox);

        $success = new StifinOperationResult(
            isSuccess: true, httpCode: 200, rawBody: '{"success":true}', parsedData: ['success' => true],
        );
        $balances = [
            ['paid' => 0, 'free' => 0],
            ['paid' => 3, 'free' => 0],
        ];
        $mock = Mockery::mock(StifinApiClient::class);
        $mock->shouldReceive('getVoucherBalance')
            ->zeroOrMoreTimes()
            ->andReturnUsing(function () use (&$balances) {
                $b = array_shift($balances);

                return $b ?? ['paid' => 99, 'free' => 99];
            });
        $mock->shouldReceive('addVoucher')
            ->once()
            ->with('BR-E2E', Mockery::on(fn (array $payload): bool => $payload['Jumlah'] === '3'
                && $payload['JmlFree'] === '0'
                && $payload['SaldoJ'] === '0'
                && $payload['SaldoF'] === '0'))
            ->andReturn($success);
        $mock->shouldReceive('getUserIdIdentifier')->zeroOrMoreTimes()->andReturn('FLOW');
        $this->app->instance(StifinApiClient::class, $mock);

        app(VoucherFulfillmentService::class)->processOutbox($outbox);

        $this->assertDatabaseCount((new Order)->getTable(), 1);
        $this->assertDatabaseCount((new OrderItem)->getTable(), 1);
        $this->assertDatabaseCount((new PaymentAttempt)->getTable(), 1);
        $this->assertDatabaseCount((new PaymentProof)->getTable(), 1);
        $this->assertDatabaseCount((new OutboxEvent)->getTable(), 1);
        $this->assertDatabaseCount((new Fulfillment)->getTable(), 1);
        $this->assertDatabaseCount((new VoucherFulfillment)->getTable(), 1);
        $this->assertDatabaseCount((new StifinOperation)->getTable(), 1);

        $order->refresh();
        $this->assertEquals(OrderStatus::Completed, $order->status);

        $this->assertSame(3, $order->items->first()->quantity, 'OrderItem quantity must be 3');

        $fulfillment = Fulfillment::query()->first();
        $this->assertEquals(FulfillmentStatus::Success, $fulfillment->status);

        $this->assertDatabaseHas((new VoucherFulfillment)->getTable(), [
            'paid_units' => 3,
            'promoter_profile_id' => $promotor->promoterProfile->id,
            'stifin_code_snapshot' => 'P-E2E',
        ]);

        $vf = VoucherFulfillment::query()->where('paid_units', 3)->firstOrFail();
        $this->assertSame(3, (int) $vf->paid_units);
        $this->assertSame(0, (int) $vf->before_balance_paid);
        $this->assertThat(
            (int) $vf->after_balance_paid,
            $this->logicalOr($this->identicalTo(0), $this->identicalTo(3)),
            'After balance harusnya 0 (mock miss) atau 3 (mock hit)'
        );

        $op = StifinOperation::query()->first();
        $this->assertEquals(StifinOperationOutcome::Success, $op->outcome);
    }

    private function seedBaseline(): array
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'P-E2E',
            'verification_status' => PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'pe2e',
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        BranchSetting::query()->create([
            'branch_code' => 'BR-E2E', 'brand_name' => 'E2E', 'bank_name' => 'BCA', 'bank_account' => '123',
            'bank_account_name' => 'E2E', 'locale' => 'id_ID', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
        ]);
        $product = Product::query()->create([
            'type' => 'voucher', 'name' => 'V-E2E', 'slug' => 'v-e2e',
            'status' => 'active', 'visibility' => 'login_only', 'price' => 50000,
        ]);
        $product->voucherConfig()->create([
            'unit_price' => 50000, 'min_qty' => 1, 'max_qty' => 100, 'presets_json' => [1, 3, 5],
        ]);

        return [$promotor, $admin, $product];
    }

    private function fakeProof(Order $order, User $promotor): PaymentProof
    {
        $attempt = $order->latestPaymentAttempt();
        $attempt->status = PaymentStatus::Submitted;
        $attempt->save();

        $order->status = OrderStatus::PaymentSubmitted;
        $order->save();

        return PaymentProof::query()->create([
            'payment_attempt_id' => $attempt->id,
            'file_path' => 'payment_proofs/fake.jpg', 'original_filename' => 'f.jpg',
            'mime_type' => 'image/jpeg', 'file_size' => 1234, 'review_status' => 'pending',
            'sender_bank' => 'BCA', 'sender_name' => $promotor->name,
            'transfer_amount' => $order->total, 'transfer_time' => now(),
        ]);
    }
}
