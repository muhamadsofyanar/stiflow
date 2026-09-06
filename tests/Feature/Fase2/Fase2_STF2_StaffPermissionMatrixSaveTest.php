<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_STF2_StaffPermissionMatrixSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_staff_permissions_page_loads(): void
    {
        Permission::query()->create(['key' => 'users.manage', 'group_key' => 'users', 'name' => 'Manage Users']);
        Permission::query()->create(['key' => 'promoters.verify', 'group_key' => 'promoters', 'name' => 'Verify Promoters']);

        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $staff = User::factory()->create([
            'role' => UserRole::Staff, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff.permissions'));
        $response->assertStatus(200);
        $response->assertSee('Permission Matrix');
    }

    public function test_staff_without_users_manage_cannot_access_permissions(): void
    {
        Permission::query()->create(['key' => 'users.manage', 'group_key' => 'users', 'name' => 'Manage Users']);
        $staff = User::factory()->create([
            'role' => UserRole::Staff, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('admin.staff.permissions'));
        $response->assertStatus(403);
    }
}
