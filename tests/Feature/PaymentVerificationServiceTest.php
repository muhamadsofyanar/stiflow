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
use RuntimeException;
use Tests\TestCase;

class PaymentVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_rejects_transfer_amount_that_does_not_match_attempt(): void
    {
        [, $admin, $order, $proof] = $this->seedBaseline();
        $proof->update(['transfer_amount' => 90000]);

        try {
            app(PaymentVerificationService::class)->approvePayment($proof, $admin, 'mismatch');
            $this->fail('Nominal yang berbeda tidak boleh disetujui.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Nominal bukti transfer tidak sama dengan tagihan.', $exception->getMessage());
        }

        $this->assertSame('pending', $proof->fresh()->review_status);
        $this->assertSame(OrderStatus::PaymentSubmitted, $order->fresh()->status);
        $this->assertSame(0, OutboxEvent::query()->count());
    }

    public function test_approval_rejects_attempt_currency_that_does_not_match_order(): void
    {
        [, $admin, $order, $proof] = $this->seedBaseline();
        $proof->paymentAttempt()->update(['currency' => 'USD']);

        try {
            app(PaymentVerificationService::class)->approvePayment($proof, $admin, 'currency mismatch');
            $this->fail('Mata uang yang berbeda tidak boleh disetujui.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Mata uang pembayaran tidak sama dengan order.', $exception->getMessage());
        }

        $this->assertSame('pending', $proof->fresh()->review_status);
        $this->assertSame(OrderStatus::PaymentSubmitted, $order->fresh()->status);
        $this->assertSame(0, OutboxEvent::query()->count());
    }

    public function test_approve_creates_outbox_once_only(): void
    {
        [$promotor, $admin, $order, $proof] = $this->seedBaseline();

        $before = OutboxEvent::query()->count();

        app(PaymentVerificationService::class)->approvePayment($proof, $admin, 'ok pertama');
        app(PaymentVerificationService::class)->approvePayment($proof, $admin, 'kedua - idempoten');

        $outbox = OutboxEvent::query()
            ->where('aggregate_type', Order::class)
            ->where('aggregate_id', $order->id)
            ->where('event_type', 'fulfill_voucher')
            ->count();

        $this->assertSame(1, $outbox - $before);
        $this->assertSame(1, $outbox);

        $order->refresh();
        $this->assertEquals(OrderStatus::Paid, $order->status);
    }

    private function seedBaseline(): array
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'PRO-V1',
            'verification_status' => PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'prv1',
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        BranchSetting::query()->create([
            'branch_code' => 'BR01', 'brand_name' => 'T', 'bank_name' => 'BCA', 'bank_account' => '1',
            'bank_account_name' => 'T', 'locale' => 'id_ID', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
        ]);

        $product = Product::query()->create([
            'type' => 'voucher', 'name' => 'VT', 'slug' => 'vt-v1',
            'status' => 'active', 'visibility' => 'login_only', 'price' => 100000,
        ]);
        $product->voucherConfig()->create([
            'unit_price' => 100000, 'min_qty' => 1, 'max_qty' => 100, 'presets_json' => [1, 5, 10],
        ]);

        $order = Order::query()->create([
            'number' => 'INV-V1-'.time(),
            'user_id' => $promotor->id,
            'promotor_code_snapshot' => 'PRO-V1',
            'currency' => 'IDR',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => OrderStatus::PaymentSubmitted,
            'expires_at' => now()->addDay(),
        ]);
        $order->items()->create([
            'product_snapshot_json' => ['name' => 'VT', 'promotor_code_target' => 'PRO-V1'],
            'quantity' => 1,
            'unit_price' => 100000,
            'discount' => 0,
            'total' => 100000,
            'fulfillment_type' => 'voucher',
        ]);
        $attempt = PaymentAttempt::query()->create([
            'order_id' => $order->id, 'method' => 'manual_transfer', 'provider' => 'internal_manual',
            'amount' => 100000, 'currency' => 'IDR', 'status' => PaymentStatus::Submitted,
        ]);
        $proof = PaymentProof::query()->create([
            'payment_attempt_id' => $attempt->id,
            'file_path' => 'payment_proofs/test.jpg',
            'original_filename' => 't.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
            'review_status' => 'pending',
            'transfer_amount' => 100000,
            'transfer_time' => now(),
        ]);

        return [$promotor, $admin, $order, $proof];
    }
}
