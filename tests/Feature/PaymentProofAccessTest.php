<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_and_admin_can_download_proof(): void
    {
        Storage::fake('private');

        [$owner, $otherPromotor, $admin, $proof] = $this->seedBaseline();

        $this->actingAs($admin)
            ->get(route('payment-proofs.download', $proof))
            ->assertStatus(200);

        $this->actingAs($owner)
            ->get(route('payment-proofs.download', $proof))
            ->assertStatus(200);

        $this->actingAs($otherPromotor)
            ->get(route('payment-proofs.download', $proof))
            ->assertStatus(403);
    }

    private function seedBaseline(): array
    {
        $owner = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $owner->promoterProfile()->create([
            'stifin_code' => 'P-OWN',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'pown',
        ]);

        $otherPromotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $otherPromotor->promoterProfile()->create([
            'stifin_code' => 'P-OTH',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'poth',
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        \App\Models\BranchSetting::query()->create([
            'branch_code' => 'BR1', 'brand_name' => 'T', 'bank_name' => 'BCA', 'bank_account' => '1',
            'bank_account_name' => 'T', 'locale' => 'id_ID', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR',
        ]);

        $order = Order::query()->create([
            'number' => 'INV-PROOF-' . time(), 'user_id' => $owner->id,
            'promotor_code_snapshot' => 'P-OWN', 'currency' => 'IDR',
            'subtotal' => 100000, 'discount' => 0, 'total' => 100000,
            'status' => OrderStatus::PaymentSubmitted, 'expires_at' => now()->addDay(),
        ]);

        $attempt = PaymentAttempt::query()->create([
            'order_id' => $order->id, 'method' => 'manual_transfer', 'provider' => 'internal_manual',
            'amount' => 100000, 'currency' => 'IDR', 'status' => PaymentStatus::Submitted,
        ]);

        $path = 'payment_proofs/owned.jpg';
        Storage::disk('private')->put($path, 'image-content');

        $proof = PaymentProof::query()->create([
            'payment_attempt_id' => $attempt->id,
            'file_path' => $path,
            'original_filename' => 'owned.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1000,
            'review_status' => 'pending',
            'transfer_amount' => 100000,
            'transfer_time' => now(),
        ]);

        return [$owner, $otherPromotor, $admin, $proof];
    }
}
