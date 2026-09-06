<?php

namespace Tests\Feature\Fase2;

use App\Enums\PayoutStatus;
use App\Enums\UserRole;
use App\Models\Payout;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACPO1_PayoutLockBeforeApproveTest extends TestCase
{
    use RefreshDatabase;

    public function test_payout_cannot_approve_if_not_locked(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'PO1-DRFT',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'po1drft',
        ]);

        $payout = Payout::query()->create([
            'promoter_profile_id' => $profile->id,
            'status' => PayoutStatus::Draft,
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.payouts.approve', $payout));
        $response->assertStatus(400);
    }

    public function test_payout_can_be_locked_and_approved(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'PO1-LCKD',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'po1lckd',
        ]);

        $payout = Payout::query()->create([
            'promoter_profile_id' => $profile->id,
            'status' => PayoutStatus::Draft,
            'total_amount' => 250000,
        ]);

        $lockResponse = $this->actingAs($admin)->post(route('admin.payouts.lock', $payout));
        $lockResponse->assertRedirect();

        $this->assertSame(PayoutStatus::Locked->value, $payout->fresh()->status->value);

        $approveResponse = $this->actingAs($admin)->post(route('admin.payouts.approve', $payout->fresh()));
        $approveResponse->assertRedirect();
        $this->assertSame(PayoutStatus::Approved->value, $payout->fresh()->status->value);
    }
}
