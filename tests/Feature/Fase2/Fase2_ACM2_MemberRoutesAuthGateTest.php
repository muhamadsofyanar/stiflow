<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACM2_MemberRoutesAuthGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_member_routes_redirect_login(): void
    {
        $this->get(route('member.dashboard'))->assertRedirect(route('login'));
        $this->get(route('member.orders.index'))->assertRedirect(route('login'));
        $this->get(route('member.kelas.index'))->assertRedirect(route('login'));
        $this->get(route('member.lisensi.index'))->assertRedirect(route('login'));
    }

    public function test_member_routes_require_auth(): void
    {
        $member = User::factory()->create([
            'role' => UserRole::Promotor,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $member->promoterProfile()->create([
            'stifin_code' => 'M2-AUTH',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'acm2auth',
        ]);
        Contact::factory()->create(['user_linked_id' => $member->id]);

        $this->actingAs($member)->get(route('member.orders.index'))->assertStatus(200);
        $this->actingAs($member)->get(route('member.hasil-stifin.index'))->assertStatus(200);
        $this->actingAs($member)->get(route('member.poin.index'))->assertStatus(200);
    }
}
