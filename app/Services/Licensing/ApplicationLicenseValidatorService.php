<?php

namespace App\Services\Licensing;

use App\Enums\LicenseStatus;
use App\Enums\LicenseTier;
use App\Models\ApplicationLicense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LogicException;

class ApplicationLicenseValidatorService
{
    private const INSTALLATION_CACHE_KEY = 'stiflow.installation_uuid';

    private const CACHE_TTL_SECONDS = 86400 * 365;

    public function getOrGenerateInstallationUuid(): string
    {
        $uuid = Cache::get(self::INSTALLATION_CACHE_KEY);
        if (! empty($uuid)) {
            return $uuid;
        }

        $dbLicense = ApplicationLicense::query()->first();
        if ($dbLicense && ! empty($dbLicense->installation_uuid)) {
            Cache::put(self::INSTALLATION_CACHE_KEY, $dbLicense->installation_uuid, self::CACHE_TTL_SECONDS);

            return $dbLicense->installation_uuid;
        }

        $uuid = $this->generateInstallationUuid();
        Cache::put(self::INSTALLATION_CACHE_KEY, $uuid, self::CACHE_TTL_SECONDS);

        return $uuid;
    }

    public function generateInstallationUuid(): string
    {
        $hostname = gethostname() ?: 'stiflow-local';
        $base = $hostname.'|'.($_SERVER['SERVER_NAME'] ?? 'localhost').'|'.config('app.key', Str::random(32));

        return Str::uuid()->toString();
    }

    public function activateLocal(string $licenseKeyPlain): array
    {
        if (app()->environment('production')) {
            throw new LogicException('Aktivasi lisensi lokal belum tersedia untuk production.');
        }

        $licenseKeyPlain = trim($licenseKeyPlain);
        if (strlen($licenseKeyPlain) < 10) {
            return ['success' => false, 'message' => 'License key terlalu pendek.'];
        }

        $hash = hash('sha256', $licenseKeyPlain);
        $installationUuid = $this->getOrGenerateInstallationUuid();

        $license = ApplicationLicense::query()->firstOrNew([
            'installation_uuid' => $installationUuid,
        ]);

        if ($license->exists && $license->license_key_sha256 !== $hash) {
            return ['success' => false, 'message' => 'License key tidak cocok dengan instalasi ini.'];
        }

        $tier = LicenseTier::Starter;
        $seats = 1;
        $branches = 1;
        if (stripos($licenseKeyPlain, 'ENT-') === 0 || stripos($licenseKeyPlain, 'ENTERPRISE') !== false) {
            $tier = LicenseTier::Enterprise;
            $seats = 999;
            $branches = 99;
        } elseif (stripos($licenseKeyPlain, 'PRO-') === 0 || stripos($licenseKeyPlain, 'PROFESSIONAL') !== false) {
            $tier = LicenseTier::Pro;
            $seats = 25;
            $branches = 10;
        }

        $expires = now()->addYears(1);
        if (Str::endsWith($licenseKeyPlain, '-LIFETIME') || Str::endsWith($licenseKeyPlain, '-LT')) {
            $expires = now()->addCentury();
        }

        $license->installation_uuid = $installationUuid;
        $license->license_key_sha256 = $hash;
        $license->domain_name = request()?->getHost() ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $license->tier = $tier;
        $license->seats_allowed = $seats;
        $license->max_branches = $branches;
        $license->activated_at = now();
        $license->expires_at = $expires;
        $license->last_validated_at = now();
        $license->status = LicenseStatus::Active;
        $license->validation_signed_lease_json = [
            'activated_via' => 'local-admin',
            'fingerprint' => $installationUuid,
            'activated_at' => now()->toIso8601String(),
            'lease_id' => Str::random(32),
        ];
        $license->lease_valid_until = now()->addDays(30);
        $license->save();

        return [
            'success' => true,
            'message' => 'Lisensi berhasil diaktifkan.',
            'tier' => $tier->value,
            'expires_at' => $expires->toIso8601String(),
        ];
    }

    public function verifyLease(): array
    {
        $installationUuid = $this->getOrGenerateInstallationUuid();
        $license = ApplicationLicense::query()
            ->where('installation_uuid', $installationUuid)
            ->first();

        if (! $license) {
            return [
                'valid' => false,
                'reason' => 'no_license',
                'tier' => LicenseTier::Starter->value,
                'message' => 'Belum ada lisensi untuk instalasi ini.',
            ];
        }

        $license->last_validated_at = now();
        $license->save();

        $now = now();
        if ($license->status !== LicenseStatus::Active) {
            return [
                'valid' => false,
                'reason' => 'status_'.$license->status->value,
                'tier' => $license->tier->value,
                'message' => 'Status lisensi: '.$license->status->value,
            ];
        }

        if ($license->expires_at !== null && $license->expires_at->isPast()) {
            return [
                'valid' => false,
                'reason' => 'expired',
                'tier' => $license->tier->value,
                'message' => 'Lisensi sudah kadaluarsa.',
            ];
        }

        if ($license->lease_valid_until !== null && $license->lease_valid_until->isPast()) {
            return [
                'valid' => false,
                'reason' => 'lease_expired',
                'tier' => $license->tier->value,
                'message' => 'Masa berlaku lease habis, perlu validasi ulang.',
            ];
        }

        return [
            'valid' => true,
            'tier' => $license->tier->value,
            'seats_allowed' => $license->seats_allowed,
            'max_branches' => $license->max_branches,
            'expires_at' => $license->expires_at?->toIso8601String(),
        ];
    }

    public function validateRemote(): array
    {
        if (app()->environment('production')) {
            throw new LogicException('Validasi lisensi remote belum diimplementasikan untuk production.');
        }

        return [
            'checked_at' => now()->toIso8601String(),
            'mode' => 'mock-offline',
            'lease_extended' => true,
            'lease_valid_until' => now()->addDays(30)->toIso8601String(),
        ];
    }
}
