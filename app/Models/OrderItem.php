<?php

namespace App\Models;

use App\Enums\FulfillmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_snapshot_json',
        'quantity',
        'unit_price',
        'discount',
        'total',
        'fulfillment_type',
    ];

    protected function casts(): array
    {
        return [
            'product_snapshot_json' => 'array',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'fulfillment_type' => FulfillmentType::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function fulfillment(): HasOne
    {
        return $this->hasOne(Fulfillment::class);
    }
}
