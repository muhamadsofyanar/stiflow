<?php

namespace Tests\Feature\Fase2;

use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACM3_MemberOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_orders_page_200(): void
    {
        $member = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $member->promoterProfile()->create([
            'stifin_code' => 'M3-ORDR',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'acm3ordr',
        ]);
        Contact::factory()->create(['user_linked_id' => $member->id]);

        $response = $this->actingAs($member)->get(route('member.orders.index'));
        $response->assertStatus(200);
    }
}
