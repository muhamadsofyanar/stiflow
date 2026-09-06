<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\ReferralLink;
use App\Models\ReferralVisit;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACA1_ReferralLinkRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_referral_redirect_sets_cookie_and_logs_visit(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'A1-REF', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'aca1ref',
        ]);

        $link = ReferralLink::query()->create([
            'promoter_profile_id' => $profile->id,
            'slug' => 'testslugaca1',
            'name' => 'Link Test ACA1',
            'is_active' => true,
            'total_visits' => 0,
        ]);

        $response = $this->get(route('referral.redirect', $link->slug));
        $response->assertRedirect('/');
        $response->assertCookie('stiflow_referral_slug', 'testslugaca1');
        $this->assertDatabaseHas('referral_visits', ['referral_link_id' => $link->id]);
        $this->assertSame(1, $link->fresh()->total_visits);
    }

    public function test_inactive_referral_link_returns_404(): void
    {
        $promotor = User::factory()->create(['role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now()]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'A1-INAC', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'aca1inac',
        ]);

        $link = ReferralLink::query()->create([
            'promoter_profile_id' => $profile->id,
            'slug' => 'inactiveaca1',
            'name' => 'Inactive Link',
            'is_active' => false,
        ]);

        $response = $this->get(route('referral.redirect', $link->slug));
        $response->assertStatus(404);
    }
}
