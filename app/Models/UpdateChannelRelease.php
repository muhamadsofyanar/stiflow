<?php

namespace App\Models;

use App\Enums\UpdateChannel;
use Illuminate\Database\Eloquent\Model;

class UpdateChannelRelease extends Model
{
    protected $fillable = [
        'version_semver',
        'channel',
        'release_notes_markdown',
        'is_critical',
        'min_php_version',
        'min_mysql_version',
        'published_at',
        'rollback_target_version',
        'changelog_json',
        'install_instructions_json',
        'file_size_mb',
        'release_signature_sha256',
    ];

    protected function casts(): array
    {
        return [
            'channel' => UpdateChannel::class,
            'is_critical' => 'boolean',
            'published_at' => 'datetime',
            'changelog_json' => 'array',
            'install_instructions_json' => 'array',
        ];
    }

    public function scopeStable($query)
    {
        return $query->where('channel', UpdateChannel::Stable);
    }

    public function scopePreview($query)
    {
        return $query->where('channel', UpdateChannel::Preview);
    }
}
