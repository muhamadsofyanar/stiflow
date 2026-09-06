<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\ReferralLink;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACA2_LeadCaptureFromCookieTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_capture_creates_contact_with_owner_from_cookie(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'A2-LEAD', 'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(), 'referral_slug' => 'aca2lead',
        ]);

        $link = ReferralLink::query()->create([
            'promoter_profile_id' => $profile->id,
            'slug' => 'leadslugaca2',
            'name' => 'Lead Capture Link',
            'is_active' => true,
            'total_leads' => 0,
        ]);

        $response = $this
            ->withCookie('stiflow_referral_slug', 'leadslugaca2')
            ->post(route('lead-capture.store'), [
                'full_name' => 'Lead Baru ACA2',
                'email' => 'leadaca2@test.com',
                'whatsapp' => '081234567890',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('contacts', [
            'full_name' => 'Lead Baru ACA2',
            'owner_promoter_profile_id' => $profile->id,
            'referred_by_promoter_profile_id' => $profile->id,
        ]);

        $this->assertSame(1, $link->fresh()->total_leads);
    }

    public function test_lead_capture_form_page_works(): void
    {
        $response = $this->get(route('lead-capture.form'));
        $response->assertStatus(200);
        $response->assertSee('Daftar & Dapatkan Hasil STIFIN');
    }
}
