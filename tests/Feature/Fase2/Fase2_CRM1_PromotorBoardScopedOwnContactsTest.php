<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_CRM1_PromotorBoardScopedOwnContactsTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotor_crm_board_page_loads(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'CRM1-BOARD',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'crm1board',
        ]);

        Contact::factory()->create([
            'full_name' => 'Contact Own Board',
            'owner_promoter_profile_id' => $profile->id,
        ]);

        $response = $this->actingAs($promotor)->get(route('promotor.crm.boards'));
        $response->assertStatus(200);
    }

    public function test_promotor_crm_index_page(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'CRM1-IDX',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'crm1idx',
        ]);

        $response = $this->actingAs($promotor)->get(route('promotor.crm.index'));
        $response->assertStatus(200);
    }
}
