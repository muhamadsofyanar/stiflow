<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACM1_MemberDashboard8CardsTest extends TestCase
{
    use RefreshDatabase;

    private function createMemberWithLinkedContact(): User
    {
        $member = User::factory()->create([
            'role' => UserRole::Promotor,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $member->promoterProfile()->create([
            'stifin_code' => 'M1-DASH',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'acm1dash',
        ]);
        Contact::factory()->create([
            'full_name' => 'Linked Contact',
            'user_linked_id' => $member->id,
        ]);

        return $member;
    }

    public function test_member_dashboard_renders_8_cards(): void
    {
        $member = $this->createMemberWithLinkedContact();

        $response = $this->actingAs($member)->get(route('member.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Pesanan');
        $response->assertSee('Kelas');
        $response->assertSee('Hasil STIFIN');
        $response->assertSee('Unduhan');
        $response->assertSee('Lisensi');
        $response->assertSee('Poin');
        $response->assertSee('Promo');
        $response->assertSee('Profil');
    }

    public function test_member_kelas_and_lisensi_pages_load(): void
    {
        $member = $this->createMemberWithLinkedContact();

        $this->actingAs($member)->get(route('member.kelas.index'))->assertStatus(200);
        $this->actingAs($member)->get(route('member.lisensi.index'))->assertStatus(200);
    }
}
