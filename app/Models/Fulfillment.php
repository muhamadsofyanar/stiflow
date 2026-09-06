<?php

namespace App\Models;

use App\Enums\FulfillmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fulfillment extends Model
{
    protected $fillable = [
        'order_item_id',
        'type',
        'status',
        'attempts',
        'correlation_id',
        'payload_snapshot_json',
        'response_snapshot_json',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => FulfillmentStatus::class,
            'attempts' => 'integer',
            'payload_snapshot_json' => 'array',
            'response_snapshot_json' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function voucherFulfillment(): HasOne
    {
        return $this->hasOne(VoucherFulfillment::class);
    }

    public function stifinOperations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StifinOperation::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [
            FulfillmentStatus::Success,
            FulfillmentStatus::Failed,
            FulfillmentStatus::NeedsReview,
        ], true);
    }
}
