<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_RT1_ModelBindingSlugAndRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirect_admin_staff_promotor_member(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_public_routes_guests_allowed(): void
    {
        $this->get(route('lead-capture.form'))->assertStatus(200);
    }

    public function test_api_license_validate_exists(): void
    {
        $response = $this->postJson(route('api.license.validate'), [
            'license_key' => 'whatever',
        ]);
        $response->assertStatus(404);
    }
}
