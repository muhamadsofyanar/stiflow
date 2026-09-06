<?php

namespace Tests\Feature\Fase2;

use App\Enums\PromoterVerificationStatus;
use App\Enums\UserRole;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACA4_PromotorVerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_promotor_403_on_verified_routes(): void
    {
        $unverified = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $unverified->promoterProfile()->create([
            'stifin_code' => 'A4-UNVR',
            'verification_status' => PromoterVerificationStatus::Pending,
            'referral_slug' => 'a4unvr',
        ]);

        $response = $this->actingAs($unverified)->get(route('promotor.dashboard'));
        $response->assertStatus(403);
    }

    public function test_verified_promotor_can_access_crm_komisi_etc(): void
    {
        $verified = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $verified->promoterProfile()->create([
            'stifin_code' => 'A4-VRFD',
            'verification_status' => PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'a4vrfd',
        ]);

        $this->actingAs($verified)->get(route('promotor.crm.index'))->assertStatus(200);
        $this->actingAs($verified)->get(route('promotor.komisi.index'))->assertStatus(200);
        $this->actingAs($verified)->get(route('promotor.referral-links.index'))->assertStatus(200);
        $this->actingAs($verified)->get(route('promotor.points.index'))->assertStatus(200);
    }
}
