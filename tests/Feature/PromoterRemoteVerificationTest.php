<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\PromoterVerificationStatus;
use App\Enums\UserRole;
use App\Integrations\Stifin\StifinApiClient;
use App\Integrations\Stifin\StifinOperationResult;
use App\Models\AuditLog;
use App\Models\BranchSetting;
use App\Models\User;
use App\Services\Promoter\PromoterVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PromoterRemoteVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_normalized_remote_code_verifies_promoter_and_records_actor(): void
    {
        [$admin, $profile] = $this->seedBaseline('KHU-ABU-02');
        $this->bindStifinResult(new StifinOperationResult(
            isSuccess: true,
            httpCode: 200,
            rawBody: '[{"KodeID":" khu-abu-02 ","Nama":"Abu"}]',
            parsedData: [['KodeID' => ' khu-abu-02 ', 'Nama' => 'Abu']],
        ));

        $verified = app(PromoterVerificationService::class)
            ->verify($profile, $admin, 'Sesuai daftar pusat');

        $this->assertSame(PromoterVerificationStatus::Verified, $verified->verification_status);
        $this->assertSame($admin->id, $verified->verified_by_user_id);
        $this->assertSame('Sesuai daftar pusat', $verified->verification_notes);
        $this->assertNotNull($verified->verified_at);

        $audit = AuditLog::query()->where('action', AuditAction::PromoterVerified)->firstOrFail();
        $this->assertSame($admin->id, $audit->actor_user_id);
        $this->assertSame($profile->id, $audit->subject_id);
    }

    public function test_absent_remote_code_leaves_promoter_pending(): void
    {
        [$admin, $profile] = $this->seedBaseline('KHU-NOT-LISTED');
        $this->bindStifinResult(new StifinOperationResult(
            isSuccess: true,
            httpCode: 200,
            rawBody: '{"data":[{"KodeID":"KHU-OTHER"}]}',
            parsedData: ['data' => [['KodeID' => 'KHU-OTHER']]],
        ));

        try {
            app(PromoterVerificationService::class)->verify($profile, $admin, 'check');
            $this->fail('Verification should fail when the promoter code is absent.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Kode promotor tidak terdaftar pada cabang STIFIN.', $exception->getMessage());
        }

        $this->assertSame(PromoterVerificationStatus::Pending, $profile->fresh()->verification_status);
        $this->assertDatabaseMissing('audit_logs', ['action' => AuditAction::PromoterVerified->value]);
    }

    public function test_remote_failure_leaves_promoter_pending(): void
    {
        [$admin, $profile] = $this->seedBaseline('KHU-ABU-02');
        $this->bindStifinResult(new StifinOperationResult(
            isSuccess: false,
            httpCode: 503,
            rawBody: '{"message":"maintenance"}',
            parsedData: ['message' => 'maintenance'],
            errorMessage: 'HTTP 503',
        ));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Daftar promotor STIFIN tidak dapat diverifikasi.');

        try {
            app(PromoterVerificationService::class)->verify($profile, $admin, null);
        } finally {
            $this->assertSame(PromoterVerificationStatus::Pending, $profile->fresh()->verification_status);
        }
    }

    public function test_verified_code_cannot_be_reused_by_another_local_account(): void
    {
        [$admin, $profile] = $this->seedBaseline('KHU-DUPLICATE');
        $otherUser = User::factory()->create([
            'role' => UserRole::Promotor,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $otherUser->promoterProfile()->create([
            'stifin_code' => ' khu-duplicate ',
            'verification_status' => PromoterVerificationStatus::Verified,
            'verified_at' => now(),
            'referral_slug' => 'existing-duplicate',
        ]);
        $this->bindStifinResult(new StifinOperationResult(
            isSuccess: true,
            httpCode: 200,
            rawBody: '[{"KodeID":"KHU-DUPLICATE"}]',
            parsedData: [['KodeID' => 'KHU-DUPLICATE']],
        ));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Kode promotor sudah digunakan oleh akun terverifikasi lain.');

        try {
            app(PromoterVerificationService::class)->verify($profile, $admin, null);
        } finally {
            $this->assertSame(PromoterVerificationStatus::Pending, $profile->fresh()->verification_status);
        }
    }

    private function seedBaseline(string $stifinCode): array
    {
        BranchSetting::query()->create([
            'branch_code' => 'KHU',
            'brand_name' => 'STIFLow KHU',
            'currency' => 'IDR',
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $promoter = User::factory()->create([
            'role' => UserRole::Promotor,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $profile = $promoter->promoterProfile()->create([
            'stifin_code' => $stifinCode,
            'verification_status' => PromoterVerificationStatus::Pending,
            'referral_slug' => strtolower(str_replace('-', '', $stifinCode)),
        ]);

        return [$admin, $profile];
    }

    private function bindStifinResult(StifinOperationResult $result): void
    {
        $client = Mockery::mock(StifinApiClient::class);
        $client->shouldReceive('listPromotersOfBranchResult')
            ->once()
            ->with('KHU')
            ->andReturn($result);
        $this->app->instance(StifinApiClient::class, $client);
    }
}
