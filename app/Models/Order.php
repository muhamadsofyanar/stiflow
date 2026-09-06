<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'number',
        'user_id',
        'promotor_code_snapshot',
        'currency',
        'subtotal',
        'discount',
        'total',
        'status',
        'referral_snapshot_json',
        'coupon_id',
        'expires_at',
        'paid_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'referral_snapshot_json' => 'array',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function latestPaymentAttempt(): ?PaymentAttempt
    {
        return $this->paymentAttempts()->latest()->first();
    }

    public function isPaidOrLater(): bool
    {
        return in_array($this->status, [
            OrderStatus::Paid,
            OrderStatus::Fulfilling,
            OrderStatus::Completed,
            OrderStatus::NeedsReview,
            OrderStatus::Refunded,
        ], true);
    }
}
