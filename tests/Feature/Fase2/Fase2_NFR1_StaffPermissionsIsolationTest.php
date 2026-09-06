<?php

namespace Tests\Feature\Fase2;

use App\Enums\ContactStatus;
use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\PointLedgerEntry;
use App\Models\PromoterProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_NFR1_StaffPermissionsIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_points_ledger_manually_added_reflects_balance(): void
    {
        $promotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $promotor->promoterProfile()->create([
            'stifin_code' => 'NFR1-PTS',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'nfr1pts',
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.points-ledger.store'), [
            'user_id' => $promotor->id,
            'direction' => 'credit',
            'amount_points' => 5000,
            'entry_type' => 'bonus',
            'notes' => 'Bonus test NFR1',
        ]);

        $response->assertRedirect();

        $this->actingAs($admin)->post(route('admin.points-ledger.store'), [
            'user_id' => $promotor->id,
            'direction' => 'debit',
            'amount_points' => 1000,
            'entry_type' => 'redemption',
            'notes' => 'Penukaran poin',
        ]);

        $this->assertSame(4000, $promotor->fresh()->pointsBalance());
    }

    public function test_admin_points_ledger_page_200(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.points-ledger.index'));
        $response->assertStatus(200);
    }
}
