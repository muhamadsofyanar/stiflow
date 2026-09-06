<?php

namespace Tests\Feature\Fase2;

use App\Enums\PromoterVerificationStatus;
use App\Enums\UserRole;
use App\Integrations\Stifin\StifinApiClient;
use App\Integrations\Stifin\StifinOperationResult;
use App\Models\BranchSetting;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class Fase2_VER1_PromotorVerifyPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_verify_promotor(): void
    {
        BranchSetting::query()->create([
            'branch_code' => 'KHU', 'brand_name' => 'STIFLow KHU', 'currency' => 'IDR',
        ]);
        Permission::query()->create(['key' => 'promoters.verify', 'group_key' => 'promoters', 'name' => 'Verify Promoters']);
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $newPromotor = User::factory()->create([
            'role' => UserRole::Promotor, 'status' => 'active', 'email_verified_at' => now(),
        ]);
        $profile = $newPromotor->promoterProfile()->create([
            'stifin_code' => 'VER1-WAIT',
            'verification_status' => PromoterVerificationStatus::Pending,
            'referral_slug' => 'ver1wait',
        ]);

        $client = Mockery::mock(StifinApiClient::class);
        $client->shouldReceive('listPromotersOfBranchResult')->once()->with('KHU')->andReturn(
            new StifinOperationResult(
                isSuccess: true,
                httpCode: 200,
                rawBody: '[{"KodeID":"VER1-WAIT"}]',
                parsedData: [['KodeID' => 'VER1-WAIT']],
            )
        );
        $this->app->instance(StifinApiClient::class, $client);

        $response = $this->actingAs($admin)->post(route('admin.promoters.verify', $profile), [
            'notes' => 'Data valid',
        ]);
        $response->assertRedirect();
        $this->assertSame(
            PromoterVerificationStatus::Verified->value,
            $profile->fresh()->verification_status->value
        );
    }
}
