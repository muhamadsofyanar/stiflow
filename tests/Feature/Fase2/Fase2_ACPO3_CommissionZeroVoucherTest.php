<?php

namespace Tests\Feature\Fase2;

use App\Enums\OrderStatus;
use App\Enums\PromoterVerificationStatus;
use App\Enums\UserRole;
use App\Models\CommissionEntry;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Affiliate\CommissionCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACPO3_CommissionZeroVoucherTest extends TestCase
{
    use RefreshDatabase;

    public function test_voucher_order_never_creates_commission_entry(): void
    {
        $promoter = User::factory()->create([
            'role' => UserRole::Promotor,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $promoter->promoterProfile()->create([
            'stifin_code' => 'PO3-ZERO',
            'verification_status' => PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'po3zero',
        ]);
        $product = Product::query()->create([
            'type' => 'voucher',
            'name' => 'Voucher tanpa komisi',
            'slug' => 'voucher-tanpa-komisi',
            'status' => 'active',
            'visibility' => 'login_only',
            'price' => 100000,
            'commission_eligible' => false,
        ]);
        $order = Order::query()->create([
            'number' => 'INV-NOCOMMISSION',
            'user_id' => $promoter->id,
            'promotor_code_snapshot' => 'PO3-ZERO',
            'currency' => 'IDR',
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_snapshot_json' => [
                'product_id' => $product->id,
                'product_type' => 'voucher',
                'commission_eligible' => false,
            ],
            'quantity' => 1,
            'unit_price' => 100000,
            'discount' => 0,
            'total' => 100000,
            'fulfillment_type' => 'voucher',
        ]);

        $result = app(CommissionCalculationService::class)->processOrder($order);

        $this->assertSame([], $result);
        $this->assertSame(0, CommissionEntry::query()->where('order_id', $order->id)->count());
    }
}
