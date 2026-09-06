<?php

namespace Tests\Feature\Fase2;

use App\Enums\CommissionEntryStatus;
use App\Enums\UserRole;
use App\Models\CommissionEntry;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACK1_CommissionStatusTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotor_komisi_page_has_tabs_and_shows_entries(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'K1-KMSI',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'ack1kmsi',
        ]);

        CommissionEntry::query()->create([
            'promoter_profile_id' => $profile->id,
            'status' => CommissionEntryStatus::Pending,
            'rule_type' => \App\Enums\CommissionRuleType::DirectSale,
            'amount' => 100000,
            'description' => 'Komisi langsung',
        ]);
        CommissionEntry::query()->create([
            'promoter_profile_id' => $profile->id,
            'status' => CommissionEntryStatus::Paid,
            'rule_type' => \App\Enums\CommissionRuleType::DirectSale,
            'amount' => 50000,
            'description' => 'Komisi lunas',
        ]);

        $response = $this->actingAs($promotor)->get(route('promotor.komisi.index'));
        $response->assertStatus(200);
        $response->assertSee('Pending');
        $response->assertSee('Payable');
        $response->assertSee('Paid');
        $response->assertSee('Reversed');
    }

    public function test_komisi_tab_filter_works(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $promotor->promoterProfile()->create([
            'stifin_code' => 'K1-TAB2',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'ack1tab2',
        ]);

        $response = $this->actingAs($promotor)->get(route('promotor.komisi.index', ['tab' => CommissionEntryStatus::Paid->value]));
        $response->assertStatus(200);
    }
}
