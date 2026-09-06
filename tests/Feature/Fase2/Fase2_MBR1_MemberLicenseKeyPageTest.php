<?php

namespace Tests\Feature\Fase2;

use App\Enums\ProductLicenseKeyStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductLicenseKey;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_MBR1_MemberLicenseKeyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_lisensi_page_200(): void
    {
        $member = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $member->promoterProfile()->create([
            'stifin_code' => 'MBR1-LIC',
            'verification_status' => \App\Enums\PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'mbr1lic',
        ]);
        Contact::factory()->create(['user_linked_id' => $member->id]);

        $product = Product::factory()->create(['type' => 'digital']);
        ProductLicenseKey::query()->create([
            'product_id' => $product->id,
            'user_id' => $member->id,
            'license_key' => 'MEMBER-LIC-TEST-001',
            'status' => ProductLicenseKeyStatus::Issued,
            'max_activations' => 3,
        ]);

        $response = $this->actingAs($member)->get(route('member.lisensi.index'));
        $response->assertStatus(200);
    }
}
