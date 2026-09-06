<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentAttempt extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'provider',
        'amount',
        'currency',
        'status',
        'external_reference',
        'reference_id_for_provider',
        'provider_response_json',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'provider_response_json' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function proof(): HasOne
    {
        return $this->hasOne(PaymentProof::class);
    }
}
