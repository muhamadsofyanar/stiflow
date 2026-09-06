<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_and_place_order_work(): void
    {
        [$promotor, $product] = $this->seedBaseline();

        $res = $this->actingAs($promotor)->get(route('promotor.checkout.index'));
        $res->assertStatus(200);
        $res->assertSee('Checkout Voucher');
        $res->assertSee($promotor->promoterProfile->stifin_code);

        $place = $this->actingAs($promotor)
            ->post(route('promotor.checkout.place-order'), ['qty' => 5]);

        $place->assertStatus(302);
        $order = \App\Models\Order::query()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame((int) $order->items->first()->quantity, 5);
        $this->assertSame($order->promotor_code_snapshot, $promotor->promoterProfile->stifin_code);
        $place->assertRedirect(route('promotor.orders.show', $order));
    }

    public function test_checkout_qty_zero_fails(): void
    {
        [$promotor] = $this->seedBaseline();
        $this->actingAs($promotor)
            ->post(route('promotor.checkout.place-order'), ['qty' => 0])
            ->assertSessionHasErrors(['qty']);
    }

    private function seedBaseline(): array
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'P-CHK',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'pchk',
        ]);
        \App\Models\BranchSetting::query()->create([
            'branch_code' => 'BR-CHK', 'brand_name' => 'T', 'bank_name' => 'BCA', 'bank_account' => '1',
            'bank_account_name' => 'T', 'locale' => 'id_ID', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
        ]);
        $product = Product::query()->create([
            'type' => 'voucher', 'name' => 'VT', 'slug' => 'vt-chk',
            'status' => 'active', 'visibility' => 'login_only', 'price' => 100000,
        ]);
        $product->voucherConfig()->create([
            'unit_price' => 100000, 'min_qty' => 1, 'max_qty' => 100, 'presets_json' => [1,5,10],
        ]);

        return [$promotor, $product];
    }
}
