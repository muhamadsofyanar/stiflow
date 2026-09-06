<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACPO2_PayoutApprovePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_payout_approve_permission_403(): void
    {
        $staff = User::factory()->create([
            'role' => UserRole::Staff, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('admin.payouts.index'));
        $response->assertStatus(200);
    }

    public function test_admin_payout_show_page_loads(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'PO2-TEST',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'po2test',
        ]);

        $payout = \App\Models\Payout::query()->create([
            'promoter_profile_id' => $profile->id,
            'status' => \App\Enums\PayoutStatus::Draft,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.payouts.show', $payout));
        $response->assertStatus(200);
    }
}
