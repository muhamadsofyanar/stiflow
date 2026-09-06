<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\StifinResult;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_STF1_StifinResultCRUDTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_stifin_result_crud(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $contact = Contact::factory()->create(['full_name' => 'Peserta STF1']);

        $store = $this->actingAs($admin)->post(route('admin.stifin-results.store'), [
            'contact_id' => $contact->id,
            'stifin_code' => 'STF1-CODE-001',
            'finest_element' => 'Wood',
            'personal_type' => 'Sanguinis',
            'learning_style' => 'Visual',
            'work_style' => 'Creative',
        ]);

        $store->assertRedirect(route('admin.stifin-results.index'));
        $this->assertDatabaseHas('stifin_results', ['stifin_code' => 'STF1-CODE-001']);

        $result = StifinResult::query()->where('stifin_code', 'STF1-CODE-001')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.stifin-results.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.stifin-results.show', $result))->assertStatus(200);
    }
}
