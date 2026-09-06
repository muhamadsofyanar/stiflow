<?php

namespace Tests\Feature\Fase2;

use App\Enums\ContactStatus;
use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACC1_ContactCRUDTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        return $admin;
    }

    public function test_admin_can_create_contact(): void
    {
        $admin = $this->createAdmin();
        $profile = PromoterProfile::factory()->create([
            'user_id' => User::factory()->create(['role' => UserRole::Promotor])->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.contacts.store'), [
            'full_name' => 'Contact Test ACC1',
            'email' => 'acc1@test.com',
            'phone' => '08123456789',
            'owner_promoter_profile_id' => $profile->id,
        ]);

        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertDatabaseHas('contacts', ['full_name' => 'Contact Test ACC1']);
    }

    public function test_admin_can_view_contact_index(): void
    {
        $admin = $this->createAdmin();
        Contact::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get(route('admin.contacts.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_update_contact(): void
    {
        $admin = $this->createAdmin();
        $contact = Contact::factory()->create(['full_name' => 'Old Name']);

        $response = $this->actingAs($admin)->put(route('admin.contacts.update', $contact), [
            'full_name' => 'Updated Name ACC1',
            'email' => 'updated@acc1.com',
        ]);

        $response->assertRedirect(route('admin.contacts.show', $contact));
        $this->assertSame('Updated Name ACC1', $contact->fresh()->full_name);
    }

    public function test_admin_can_delete_contact(): void
    {
        $admin = $this->createAdmin();
        $contact = Contact::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.contacts.destroy', $contact));
        $response->assertRedirect(route('admin.contacts.index'));
        $this->assertSoftDeleted($contact);
    }
}
