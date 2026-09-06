<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACP1_Permission403Test extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_permission_cannot_access_integrations(): void
    {
        $staff = User::factory()->create([
            'role' => UserRole::Staff,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $staff->promoterProfile()->create([
            'stifin_code' => 'P-ACP1-' . $staff->id,
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'acp1' . $staff->id,
        ]);

        $response = $this->actingAs($staff)->get(route('admin.integrations.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_integrations(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.integrations.index'));
        $response->assertStatus(200);
    }
}
