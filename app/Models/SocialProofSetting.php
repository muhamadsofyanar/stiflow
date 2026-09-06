<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialProofSetting extends Model
{
    protected $fillable = [
        'product_id',
        'show_recent_purchase',
        'time_window_hours',
        'display_limit',
        'anonymize_name',
    ];

    protected function casts(): array
    {
        return [
            'show_recent_purchase' => 'boolean',
            'anonymize_name' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
