<?php

namespace Tests\Feature\Fase2;

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACCO1_CampaignSchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_campaign_crud_and_scheduling_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $template = MessageTemplate::query()->create([
            'name' => 'Template CO1',
            'channel' => 'email',
            'body' => 'Halo {{name}}',
            'is_system' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.campaigns.store'), [
            'name' => 'Campaign September CO1',
            'channel' => 'email',
            'template_id' => $template->id,
            'status' => CampaignStatus::Draft->value,
        ]);
        $response->assertRedirect(route('admin.campaigns.index'));
        $this->assertDatabaseHas('campaigns', ['name' => 'Campaign September CO1']);

        $getResponse = $this->actingAs($admin)->get(route('admin.campaigns.index'));
        $getResponse->assertStatus(200);
    }
}
