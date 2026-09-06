<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherFulfillment extends Model
{
    protected $fillable = [
        'fulfillment_id',
        'promoter_profile_id',
        'stifin_code_snapshot',
        'paid_units',
        'free_units',
        'balance_before_paid',
        'balance_before_free',
        'balance_after_paid',
        'balance_after_free',
        'stifin_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'paid_units' => 'integer',
            'free_units' => 'integer',
            'balance_before_paid' => 'integer',
            'balance_before_free' => 'integer',
            'balance_after_paid' => 'integer',
            'balance_after_free' => 'integer',
        ];
    }

    public function fulfillment(): BelongsTo
    {
        return $this->belongsTo(Fulfillment::class);
    }

    public function promoterProfile(): BelongsTo
    {
        return $this->belongsTo(PromoterProfile::class);
    }
}
