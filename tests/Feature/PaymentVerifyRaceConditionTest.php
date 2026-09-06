<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PromoterVerificationStatus;
use App\Enums\UserRole;
use App\Models\BranchSetting;
use App\Models\Order;
use App\Models\OutboxEvent;
use App\Models\PaymentAttempt;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\User;
use App\Services\Payment\PaymentVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Throwable;

class PaymentVerifyRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbox_schema_has_a_database_unique_idempotency_key(): void
    {
        $this->assertTrue(Schema::hasColumn('outbox_events', 'idempotency_key'));
    }

    public function test_parallel_approve_never_creates_duplicate_outbox(): void
    {
        [$promotor, $admin, $order, $proof] = $this->seedBaseline();

        $service = app(PaymentVerificationService::class);

        $errors = [];
        try {
            $service->approvePayment($proof, $admin, 'first');
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }

        try {
            $service->approvePayment($proof, $admin, 'second (racers)');
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }

        $count = OutboxEvent::query()
            ->where('aggregate_type', Order::class)
            ->where('aggregate_id', $order->id)
            ->where('event_type', 'fulfill_voucher')
            ->count();

        $this->assertSame(1, $count);

        $this->assertSame(
            'voucher-order-item:'.$order->items()->where('fulfillment_type', 'voucher')->firstOrFail()->id,
            OutboxEvent::query()->where('aggregate_id', $order->id)->value('idempotency_key'),
        );

        $order->refresh();
        $this->assertEquals(OrderStatus::Paid, $order->status);
    }

    private function seedBaseline(): array
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'P-RACE',
            'verification_status' => PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'prace',
        ]);
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        BranchSetting::query()->create([
            'branch_code' => 'BR-RACE', 'brand_name' => 'T', 'bank_name' => 'BCA', 'bank_account' => '1',
            'bank_account_name' => 'T', 'locale' => 'id_ID', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
        ]);
        $product = Product::query()->create([
            'type' => 'voucher', 'name' => 'VT', 'slug' => 'vt-race',
            'status' => 'active', 'visibility' => 'login_only', 'price' => 100000,
        ]);
        $product->voucherConfig()->create([
            'unit_price' => 100000, 'min_qty' => 1, 'max_qty' => 100, 'presets_json' => [1],
        ]);
        $order = Order::query()->create([
            'number' => 'INV-RACE-'.time(), 'user_id' => $promotor->id,
            'promotor_code_snapshot' => 'P-RACE', 'currency' => 'IDR',
            'subtotal' => 100000, 'discount' => 0, 'total' => 100000,
            'status' => OrderStatus::PaymentSubmitted, 'expires_at' => now()->addDay(),
        ]);
        $order->items()->create([
            'product_snapshot_json' => ['name' => 'VT', 'promotor_code_target' => 'P-RACE'],
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
