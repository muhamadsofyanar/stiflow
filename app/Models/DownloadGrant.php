<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'digital_asset_id',
        'order_id',
        'order_item_id',
        'user_id',
        'enrollment_id',
        'max_downloads',
        'downloads_made_count',
        'expires_at',
        'first_downloaded_at',
        'last_downloaded_at',
        'asset_version_snapshot',
        'checksum_snapshot',
        'is_revoked',
        'revocation_reason',
        'ip_address_bound',
        'user_agent_bound_fingerprint',
        'grant_source',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'first_downloaded_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    public function digitalAsset()
    {
        return $this->belongsTo(DigitalAsset::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function downloadLogs()
    {
        return $this->hasMany(AssetDownloadLog::class);
    }
}
