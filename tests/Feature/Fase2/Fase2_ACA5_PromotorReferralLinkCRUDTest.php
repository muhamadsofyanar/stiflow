<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\ReferralLink;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACA5_PromotorReferralLinkCRUDTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotor_can_create_referral_link(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'A5-RLNK',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'aca5rlnk',
        ]);

        $response = $this->actingAs($promotor)->post(route('promotor.referral-links.store'), [
            'name' => 'Link Instagram ACA5',
            'utm_source' => 'instagram',
            'utm_medium' => 'social',
            'utm_campaign' => 'launch_sep',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('referral_links', ['name' => 'Link Instagram ACA5', 'utm_source' => 'instagram']);
    }

    public function test_promotor_referral_links_index_page(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'A5-IDX',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'aca5idx',
        ]);

        $response = $this->actingAs($promotor)->get(route('promotor.referral-links.index'));
        $response->assertStatus(200);
    }
}
