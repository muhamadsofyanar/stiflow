<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\PromoterProfile;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACA3_SponsorTreeMultiLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotor_affiliate_tree_page_loads(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'A3-TREE', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'aca3tree',
        ]);

        $response = $this->actingAs($promotor)->get(route('promotor.affiliate.tree'));
        $response->assertStatus(200);
        $response->assertSee('Pohon Sponsor');
    }
}
