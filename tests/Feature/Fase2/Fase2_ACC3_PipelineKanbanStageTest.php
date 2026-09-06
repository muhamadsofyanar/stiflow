<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACC3_PipelineKanbanStageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pipeline_board_renders_stages(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $pipeline = Pipeline::factory()->create(['name' => 'Sales Pipeline', 'is_active' => true]);
        $stageNew = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id, 'name' => 'New', 'position' => 1]);
        $stageWon = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id, 'name' => 'Won', 'position' => 2]);
        Contact::factory()->create(['stage_id' => $stageNew->id, 'pipeline_id' => $pipeline->id]);

        $response = $this->actingAs($admin)->get(route('admin.pipelines.show', $pipeline));
        $response->assertStatus(200);
        $response->assertSee('Sales Pipeline');
        $response->assertSee('New');
    }

    public function test_pipeline_crud_routes(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pipelines.store'), [
            'name' => 'Pipeline ACC3',
            'is_active' => true,
        ]);
        $response->assertRedirect(route('admin.pipelines.index'));
        $this->assertDatabaseHas('pipelines', ['name' => 'Pipeline ACC3']);
    }
}
