<?php

namespace Tests\Feature\Fase2;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\UserRole;
use App\Models\IntegrationConnection;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_AFCI1_IntegrationCRUDAndPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_integration_crud_routes(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $create = $this->actingAs($admin)->post(route('admin.integrations.store'), [
            'provider' => 'Mailgun',
            'category' => 'email',
            'name' => 'Mailgun Primary',
            'status' => IntegrationConnectionStatus::Pending->value,
        ]);
        $create->assertRedirect(route('admin.integrations.index'));
        $this->assertDatabaseHas('integration_connections', ['name' => 'Mailgun Primary']);

        $int = IntegrationConnection::query()->where('name', 'Mailgun Primary')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.integrations.edit', $int))->assertStatus(200);

        $update = $this->actingAs($admin)->put(route('admin.integrations.update', $int), [
            'provider' => 'Sendgrid',
            'category' => 'email',
            'name' => 'Sendgrid Updated I1',
            'status' => IntegrationConnectionStatus::Connected->value,
        ]);
        $update->assertRedirect(route('admin.integrations.index'));
        $this->assertSame('Sendgrid Updated I1', $int->fresh()->name);
    }

    public function test_staff_without_integrations_manage_permission_403(): void
    {
        Permission::query()->create(['key' => 'integrations.manage', 'group_key' => 'integrations', 'name' => 'Manage Integrations']);

        $staff = User::factory()->create([
            'role' => UserRole::Staff, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('admin.integrations.index'));
        $response->assertStatus(403);
    }
}
