<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetDownloadLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'download_grant_id',
        'digital_asset_id',
        'user_id',
        'token_used',
        'ip_address',
        'user_agent',
        'success_flag',
        'denial_reason',
        'bytes_sent',
        'occurred_at',
    ];

    protected $casts = [
        'success_flag' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function downloadGrant()
    {
        return $this->belongsTo(DownloadGrant::class);
    }

    public function digitalAsset()
    {
        return $this->belongsTo(DigitalAsset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
