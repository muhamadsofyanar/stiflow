<?php

namespace App\Models;

use App\Enums\PixelProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTrackingPixel extends Model
{
    protected $fillable = [
        'provider',
        'pixel_id',
        'is_active',
        'script_head',
        'script_body',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PixelProvider::class,
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
