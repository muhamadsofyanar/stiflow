<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACCO2_MessageTemplateCRUDTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_templates_page_and_crud(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        MessageTemplate::query()->create([
            'name' => 'Template Email Welcome CO2',
            'channel' => 'email',
            'subject' => 'Selamat datang di STIFLOW',
            'body' => 'Halo pelanggan tercinta, selamat bergabung!',
            'is_system' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.templates.index'));
        $response->assertStatus(200);
        $response->assertSee('Template Email Welcome CO2');

        $storeResponse = $this->actingAs($admin)->post(route('admin.templates.store'), [
            'name' => 'Template SMS CO2',
            'channel' => 'sms',
            'body' => 'Halo, ini SMS dari STIFLOW.',
        ]);
        $storeResponse->assertRedirect(route('admin.templates.index'));
        $this->assertDatabaseHas('message_templates', ['name' => 'Template SMS CO2']);
    }
}
