<?php

namespace App\Services\Promoter;

use App\Enums\AuditAction;
use App\Enums\PromoterVerificationStatus;
use App\Integrations\Stifin\StifinApiClient;
use App\Models\BranchSetting;
use App\Models\PromoterProfile;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PromoterVerificationService
{
    public function __construct(private readonly StifinApiClient $stifinApiClient) {}

    /**
     * @throws AuthorizationException
     * @throws RuntimeException
     */
    public function verify(PromoterProfile $profile, User $actor, ?string $notes = null): PromoterProfile
    {
        if (! $actor->isStaffOrAbove()) {
            throw new AuthorizationException('Anda tidak berwenang memverifikasi promotor.');
        }

        $branchCode = trim(BranchSetting::current()->branch_code);
        $result = $this->stifinApiClient->listPromotersOfBranchResult($branchCode);

        if (! $result->isSuccess || ! is_array($result->parsedData)) {
            throw new RuntimeException('Daftar promotor STIFIN tidak dapat diverifikasi.');
        }

        $remotePromoters = $this->extractPromoterList($result->parsedData);
        $normalizedCode = $this->normalizeCode($profile->stifin_code);
        $isListed = collect($remotePromoters)->contains(function (mixed $remote) use ($normalizedCode): bool {
            if (! is_array($remote)) {
                return false;
            }

            $remoteCode = $remote['KodeID'] ?? $remote['kode_id'] ?? $remote['stifin_code'] ?? $remote['code'] ?? null;

            return is_scalar($remoteCode) && $this->normalizeCode((string) $remoteCode) === $normalizedCode;
        });

        if (! $isListed) {
            throw new RuntimeException('Kode promotor tidak terdaftar pada cabang STIFIN.');
        }

        return DB::transaction(function () use ($profile, $actor, $notes, $normalizedCode): PromoterProfile {
            $lockedProfile = PromoterProfile::query()->lockForUpdate()->findOrFail($profile->getKey());

            $duplicateExists = PromoterProfile::query()
                ->whereKeyNot($lockedProfile->getKey())
                ->where('verification_status', PromoterVerificationStatus::Verified)
                ->whereRaw('UPPER(TRIM(stifin_code)) = ?', [$normalizedCode])
                ->exists();

            if ($duplicateExists) {
                throw new RuntimeException('Kode promotor sudah digunakan oleh akun terverifikasi lain.');
            }

            $before = [
                'verification_status' => $lockedProfile->verification_status->value,
                'verified_at' => $lockedProfile->verified_at?->toAtomString(),
                'verified_by_user_id' => $lockedProfile->verified_by_user_id,
            ];

            $lockedProfile->fill([
                'verification_status' => PromoterVerificationStatus::Verified,
                'verified_at' => now(),
                'verified_by_user_id' => $actor->getKey(),
                'verification_notes' => $notes,
            ])->save();

            AuditService::record(
                action: AuditAction::PromoterVerified,
                subject: $lockedProfile,
                before: $before,
                after: [
                    'verification_status' => PromoterVerificationStatus::Verified->value,
                    'verified_at' => $lockedProfile->verified_at?->toAtomString(),
                    'verified_by_user_id' => $actor->getKey(),
                ],
                metadata: ['branch_code' => BranchSetting::current()->branch_code],
                actor: $actor,
            );

            return $lockedProfile->refresh();
        });
    }

    /** @return list<array> */
    private function extractPromoterList(array $payload): array
    {
        foreach (['data', 'Data', 'result', 'Result'] as $wrapper) {
            if (isset($payload[$wrapper]) && is_array($payload[$wrapper])) {
                $payload = $payload[$wrapper];
                break;
            }
        }

        if (array_is_list($payload)) {
            return array_values(array_filter($payload, 'is_array'));
        }

        return [$payload];
    }

    private function normalizeCode(string $code): string
    {
        return mb_strtoupper(trim($code), 'UTF-8');
    }
}
