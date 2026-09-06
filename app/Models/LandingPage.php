<?php

namespace App\Models;

use App\Enums\LandingPageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'status',
        'blocks_json',
        'meta_json',
        'published_at',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => LandingPageStatus::class,
            'blocks_json' => 'array',
            'meta_json' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(LandingPageVisit::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isPublished(): bool
    {
        return $this->status === LandingPageStatus::Published;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
