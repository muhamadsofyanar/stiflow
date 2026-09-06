<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotor_accessing_admin_routes_returns_403(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'P-AUTH-' . $promotor->id,
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'au' . $promotor->id,
        ]);

        $response = $this->actingAs($promotor)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($promotor)->get(route('admin.orders.index'));
        $response2->assertStatus(403);

        $response3 = $this->actingAs($promotor)->get(route('admin.promotors.index'));
        $response3->assertStatus(403);
    }

    public function test_guest_accessing_protected_routes_redirected(): void
    {
        $this->get(route('promotor.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
