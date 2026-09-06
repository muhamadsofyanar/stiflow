<?php

namespace App\Services\Update;

use App\Enums\UpdateChannel;
use App\Models\UpdateChannelRelease;
use Illuminate\Support\Facades\Cache;

class UpdateChannelCheckerService
{
    private const CACHE_KEY = 'stiflow.latest_release.';

    public function checkForNewRelease(?string $channel = null): ?UpdateChannelRelease
    {
        $ch = $channel ?? UpdateChannel::Stable->value;

        $seed = [
            ['version' => '1.0.0', 'channel' => 'Stable', 'notes' => 'Initial release STIFLOW Cabang Edition.', 'critical' => false, 'size' => 42],
            ['version' => '1.1.0', 'channel' => 'Stable', 'notes' => 'Perbaikan voucher fulfillment + LMS progress tracking.', 'critical' => false, 'size' => 48],
            ['version' => '1.2.0', 'channel' => 'Stable', 'notes' => 'Public katalog produk, landing page builder, analytics dashboard.', 'critical' => false, 'size' => 55],
            ['version' => '1.3.0', 'channel' => 'Stable', 'notes' => 'Payment gateway adapters + social proof + pixel tracking.', 'critical' => false, 'size' => 60],
            ['version' => '1.4.0-beta1', 'channel' => 'Preview', 'notes' => 'Preview: Multi-branch reconciliation + advanced automation.', 'critical' => true, 'size' => 72],
        ];

        foreach ($seed as $item) {
            UpdateChannelRelease::query()->updateOrCreate(
                ['version_semver' => $item['version']],
                [
                    'channel' => $item['channel'],
                    'release_notes_markdown' => $item['notes'],
                    'is_critical' => $item['critical'],
                    'published_at' => now()->subDays(rand(1, 120)),
                    'min_php_version' => '8.2.0',
                    'min_mysql_version' => '8.0',
                    'file_size_mb' => $item['size'],
                    'changelog_json' => [
                        ['type' => 'added', 'desc' => $item['notes']],
                        ['type' => 'security', 'desc' => 'Dependency patches.'],
                    ],
                    'install_instructions_json' => [
                        'backup' => true,
                        'maintenance' => false,
                        'steps' => ['Download', 'Extract', 'Run migrate', 'Clear cache'],
                    ],
                    'release_signature_sha256' => hash('sha256', $item['version'].'stiflow-salt'),
                ]
            );
        }

        return $this->getLatestRelease($ch);
    }

    public function getLatestRelease(string $channel = 'Stable'): ?UpdateChannelRelease
    {
        $cacheKey = self::CACHE_KEY.$channel;
        $cached = Cache::get($cacheKey);
        if ($cached instanceof UpdateChannelRelease) {
            return $cached;
        }

        $release = UpdateChannelRelease::query()
            ->where('channel', $channel)
            ->orderByDesc('published_at')
            ->first();

        if ($release) {
            Cache::put($cacheKey, $release, 3600);
        }

        return $release;
    }

    public function isNewerVersionAvailable(string $currentVersion, string $channel = 'Stable'): bool
    {
        $latest = $this->getLatestRelease($channel);
        if (! $latest) {
            return false;
        }

        return version_compare($latest->version_semver, $currentVersion, '>');
    }
}
