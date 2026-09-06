<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Fase2_ACC4_ContactImportPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_contacts_page_available(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.contacts.create'));
        $response->assertStatus(200);
    }
}
