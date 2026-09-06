<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACM4_PromotorMemberAreaPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotor_member_pages_all_200(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'M4-ALL',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'acm4all',
        ]);
        Contact::factory()->create(['user_linked_id' => $promotor->id]);

        $this->actingAs($promotor)->get(route('promotor.kelas-saya.index'))->assertStatus(200);
        $this->actingAs($promotor)->get(route('promotor.hasil-stifin.index'))->assertStatus(200);
        $this->actingAs($promotor)->get(route('promotor.unduhan.index'))->assertStatus(200);
        $this->actingAs($promotor)->get(route('promotor.points.index'))->assertStatus(200);
    }

    public function test_member_unduhan_and_profil_pages(): void
    {
        $member = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $member->promoterProfile()->create([
            'stifin_code' => 'M4-UDH',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'acm4udh',
        ]);
        Contact::factory()->create(['user_linked_id' => $member->id]);

        $this->actingAs($member)->get(route('member.unduhan.index'))->assertStatus(200);
        $this->actingAs($member)->get(route('member.profil.index'))->assertStatus(200);
    }
}
