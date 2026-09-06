<?php

namespace App\Services\Fulfillment;

use App\Enums\AuditAction;
use App\Models\AssetDownloadLog;
use App\Models\DigitalAsset;
use App\Models\DownloadGrant;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadGrantService
{
    public function createGrant(
        DigitalAsset $asset,
        array $context = [],
        int $maxDownloads = 5,
        ?int $expiryHours = 72,
    ): DownloadGrant {
        return DB::transaction(function () use ($asset, $context, $maxDownloads, $expiryHours) {
            $token = $this->generateUniqueToken();

            $expiresAt = null;
            if ($expiryHours !== null && $expiryHours > 0) {
                $expiresAt = now()->addHours($expiryHours);
            }

            $grant = DownloadGrant::query()->create([
                'token' => $token,
                'digital_asset_id' => $asset->id,
                'order_id' => $context['order_id'] ?? null,
                'order_item_id' => $context['order_item_id'] ?? null,
                'user_id' => $context['user_id'] ?? null,
                'enrollment_id' => $context['enrollment_id'] ?? null,
                'max_downloads' => $maxDownloads,
                'downloads_made_count' => 0,
                'expires_at' => $expiresAt,
                'first_downloaded_at' => null,
                'last_downloaded_at' => null,
                'asset_version_snapshot' => $asset->version,
                'checksum_snapshot' => $asset->checksum_sha256,
                'is_revoked' => false,
                'revocation_reason' => null,
                'ip_address_bound' => $context['ip_address_bound'] ?? null,
                'user_agent_bound_fingerprint' => $context['user_agent_bound_fingerprint'] ?? null,
                'grant_source' => $context['grant_source'] ?? 'manual',
            ]);

            AuditService::record(
                action: AuditAction::ProductCreated ?? 'download_grant.created',
                subject: $grant,
                after: [
                    'digital_asset_id' => $asset->id,
                    'asset_name' => $asset->name,
                    'max_downloads' => $maxDownloads,
                    'expires_at' => $expiresAt?->toIso8601String(),
                ],
                actor: $context['actor'] ?? null,
            );

            return $grant;
        });
    }

    public function redeemForToken(string $token, array $context = []): StreamedResponse
    {
        return DB::transaction(function () use ($token, $context) {
            $grant = DownloadGrant::query()
                ->where('token', $token)
                ->lockForUpdate()
                ->first();

            if (! $grant) {
                abort(403, 'Token download tidak valid.');
            }

            if ($grant->is_revoked) {
                abort(403, 'Token download sudah dicabut.');
            }

            if ($grant->expires_at !== null && $grant->expires_at->isPast()) {
                abort(403, 'Token download sudah kadaluarsa.');
            }

            if ($grant->max_downloads > 0 && (int) $grant->downloads_made_count >= (int) $grant->max_downloads) {
                abort(403, 'Batas download sudah tercapai.');
            }

            $asset = DigitalAsset::query()->find($grant->digital_asset_id);
            if (! $asset) {
                abort(403, 'Aset digital tidak ditemukan.');
            }

            $grant->downloads_made_count = (int) $grant->downloads_made_count + 1;
            if ($grant->first_downloaded_at === null) {
                $grant->first_downloaded_at = now();
            }
            $grant->last_downloaded_at = now();
            $grant->save();

            AssetDownloadLog::query()->create([
                'download_grant_id' => $grant->id,
                'digital_asset_id' => $asset->id,
                'user_id' => $grant->user_id,
                'downloaded_at' => now(),
                'ip_address' => $context['ip_address'] ?? request()?->ip(),
                'user_agent' => $context['user_agent'] ?? request()?->userAgent(),
                'download_number' => $grant->downloads_made_count,
                'checksum_verified' => null,
            ]);

            $disk = $asset->storage_disk ? Storage::disk($asset->storage_disk) : Storage::disk('private');
            $path = $asset->storage_path;

            if (! $disk->exists($path)) {
                abort(404, 'File tidak ditemukan.');
            }

            $headers = [
                'Content-Type' => $asset->mime_type ?? 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . ($asset->original_filename ?? 'download') . '"',
            ];

            return response()->streamDownload(function () use ($disk, $path) {
                $stream = $disk->readStream($path);
                if ($stream === false) {
                    throw new RuntimeException('Gagal membaca file.');
                }
                fpassthru($stream);
                fclose($stream);
            }, $asset->original_filename ?? 'download', $headers);
        });
    }

    public function revokeGrant(DownloadGrant $grant, string $reason = ''): DownloadGrant
    {
        return DB::transaction(function () use ($grant, $reason) {
            $grant->is_revoked = true;
            $grant->revocation_reason = $reason;
            $grant->save();

            AuditService::record(
                action: AuditAction::ProductUpdated ?? 'download_grant.revoked',
                subject: $grant,
                after: [
                    'revocation_reason' => $reason,
                ],
            );

            return $grant;
        });
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (DownloadGrant::query()->where('token', $token)->exists());

        return $token;
    }
}
