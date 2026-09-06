<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACP2_StaffPermissionScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_with_permission_can_access_audit_logs(): void
    {
        Permission::query()->create(['key' => 'audit.view', 'group_key' => 'system', 'name' => 'View Audit Logs']);
        $staff = User::factory()->create([
            'role' => UserRole::Staff,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $staff->permissions()->attach(Permission::query()->where('key', 'audit.view')->first()->id);

        $response = $this->actingAs($staff)->get(route('admin.audit.index'));
        $response->assertStatus(200);
    }

    public function test_staff_without_audit_permission_returns_403(): void
    {
        $staff = User::factory()->create([
            'role' => UserRole::Staff,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('admin.audit.index'));
        $response->assertStatus(403);
    }
}
