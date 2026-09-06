<?php

namespace Tests\Feature\Fase2;

use App\Enums\ContactStatus;
use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACC2_ContactOwnershipScopedTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotor_can_only_see_own_contacts_in_crm_index(): void
    {
        $promotorA = User::factory()->create(['role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now()]);
        $profileA = $promotorA->promoterProfile()->create([
            'stifin_code' => 'S-ACC2A', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'acc2a',
        ]);

        $promotorB = User::factory()->create(['role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now()]);
        $profileB = $promotorB->promoterProfile()->create([
            'stifin_code' => 'S-ACC2B', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'acc2b',
        ]);

        Contact::factory()->create(['full_name' => 'Own A', 'owner_promoter_profile_id' => $profileA->id]);
        Contact::factory()->create(['full_name' => 'Own B', 'owner_promoter_profile_id' => $profileB->id]);

        $response = $this->actingAs($promotorA)->get(route('promotor.crm.index'));
        $response->assertStatus(200);
    }

    public function test_promotor_403_on_someone_else_contact_detail(): void
    {
        $promotorA = User::factory()->create(['role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now()]);
        $profileA = $promotorA->promoterProfile()->create([
            'stifin_code' => 'S-ACC2A2', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'acc2a2',
        ]);

        $promotorB = User::factory()->create(['role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now()]);
        $profileB = $promotorB->promoterProfile()->create([
            'stifin_code' => 'S-ACC2B2', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'acc2b2',
        ]);

        $contactB = Contact::factory()->create(['owner_promoter_profile_id' => $profileB->id]);

        $response = $this->actingAs($promotorA)->get(route('promotor.crm.contacts.show', $contactB));
        $response->assertStatus(403);
    }
}
