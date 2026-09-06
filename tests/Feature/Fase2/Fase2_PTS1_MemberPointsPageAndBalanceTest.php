<?php

namespace Tests\Feature\Fase2;

use App\Enums\PointDirection;
use App\Enums\PointEntryType;
use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\PointLedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_PTS1_MemberPointsPageAndBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_poin_page_200_and_shows_balance(): void
    {
        $member = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $member->promoterProfile()->create([
            'stifin_code' => 'PTS1-MBR',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'pts1mbr',
        ]);
        Contact::factory()->create(['user_linked_id' => $member->id]);

        PointLedgerEntry::query()->create([
            'user_id' => $member->id,
            'direction' => PointDirection::Credit,
            'entry_type' => PointEntryType::OrderBonus,
            'amount_points' => 2500,
            'notes' => 'Bonus signup',
        ]);

        $response = $this->actingAs($member)->get(route('member.poin.index'));
        $response->assertStatus(200);
        $this->assertSame(2500, $member->fresh()->pointsBalance());
    }
}
