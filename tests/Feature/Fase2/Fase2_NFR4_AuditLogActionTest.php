<?php

namespace Tests\Feature\Fase2;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_NFR4_AuditLogActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_staff_with_permission_can_view(): void
    {
        Permission::query()->create(['key' => 'audit.view', 'group_key' => 'system', 'name' => 'View Audit']);
        $staff = User::factory()->create([
            'role' => UserRole::Staff, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $staff->permissions()->attach(Permission::query()->where('key', 'audit.view')->first()->id);

        AuditLog::query()->create([
            'actor_user_id' => $staff->id,
            'action' => AuditAction::Created,
            'resource_type' => 'contact',
            'resource_id' => '99',
        ]);

        $response = $this->actingAs($staff)->get(route('admin.audit.index'));
        $response->assertStatus(200);
    }

    public function test_audit_log_requires_permission(): void
    {
        $staffNoPerm = User::factory()->create([
            'role' => UserRole::Staff, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staffNoPerm)->get(route('admin.audit.index'));
        $response->assertStatus(403);
    }
}
