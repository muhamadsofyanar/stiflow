<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_NAV1_AdminNavigationDropdownsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_navigation_shows_new_menus(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('CRM');
        $response->assertSee('Affiliate');
        $response->assertSee('LMS');
        $response->assertSee('Komunikasi');
        $response->assertSee('Integrations');
    }

    public function test_promotor_navigation_shows_business_and_member_menus(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'NAV1-PRM',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'nav1prm',
        ]);

        $response = $this->actingAs($promotor)->get(route('promotor.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('CRM');
        $response->assertSee('Bisnis');
        $response->assertSee('Member Area');
    }
}
