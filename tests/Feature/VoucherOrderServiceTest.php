<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\PromoterProfile;
use App\Models\User;
use App\Services\Voucher\VoucherOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_target_voucher_code_always_from_promotor_profile_not_input(): void
    {
        [$promotor, , $product] = $this->seedBaseline();

        $order = app(VoucherOrderService::class)
            ->createVoucherOrder($promotor, $product, 5);

        $this->assertSame($promotor->promoterProfile->stifin_code, $order->promotor_code_snapshot);

        $item = $order->items->first();
        $snapshotTarget = $item->product_snapshot_json['promotor_code_target'] ?? null;
        $this->assertSame($promotor->promoterProfile->stifin_code, $snapshotTarget);
        $this->assertNotSame('CLIENT-INJECTED-123', $snapshotTarget);
        $this->assertEquals(OrderStatus::PendingPayment, $order->status);
    }

    public function test_quantity_out_of_range_throws(): void
    {
        [$promotor, , $product] = $this->seedBaseline();

        $this->expectException(\InvalidArgumentException::class);
        app(VoucherOrderService::class)->createVoucherOrder($promotor, $product, 99999);
    }

    private function seedBaseline(): array
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'PROMO-' . $promotor->id,
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'slug-' . $promotor->id,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        \App\Models\BranchSetting::query()->create([
            'branch_code' => 'BR01',
            'brand_name' => 'Test',
            'bank_name' => 'BCA',
            'bank_account' => '123',
            'bank_account_name' => 'T',
            'locale' => 'id_ID',
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
        ]);

        $product = Product::query()->create([
            'type' => 'voucher',
            'name' => 'V Test',
            'slug' => 'vt-' . $promotor->id,
            'status' => 'active',
            'visibility' => 'login_only',
            'price' => 100000,
            'commission_eligible' => false,
        ]);
        $product->voucherConfig()->create([
            'unit_price' => 100000,
            'min_qty' => 1,
            'max_qty' => 100,
            'presets_json' => [1, 5, 10],
        ]);

        return [$promotor, $admin, $product];
    }
}
