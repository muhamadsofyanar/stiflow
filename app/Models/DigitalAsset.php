<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'course_id',
        'lesson_id',
        'uploaded_by_user_id',
        'asset_type',
        'name',
        'description',
        'storage_disk',
        'storage_path',
        'original_filename',
        'mime_type',
        'file_size_bytes',
        'checksum_sha256',
        'version',
        'is_published',
        'download_limit_default',
        'expiry_hours_default',
        'replaces_asset_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function replacesAsset()
    {
        return $this->belongsTo(DigitalAsset::class, 'replaces_asset_id');
    }

    public function replacementAssets()
    {
        return $this->hasMany(DigitalAsset::class, 'replaces_asset_id');
    }

    public function downloadGrants()
    {
        return $this->hasMany(DownloadGrant::class);
    }

    public function downloadLogs()
    {
        return $this->hasMany(AssetDownloadLog::class);
    }

    public function courseCoverImages()
    {
        return $this->hasMany(Course::class, 'cover_image_id');
    }
}
