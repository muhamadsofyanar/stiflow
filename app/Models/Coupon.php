<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_discount',
        'min_order_total',
        'max_redemptions_global',
        'max_redemptions_per_user',
        'product_scope_json',
        'user_scope_json',
        'status',
        'starts_at',
        'expires_at',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'min_order_total' => 'decimal:2',
            'max_redemptions_global' => 'integer',
            'max_redemptions_per_user' => 'integer',
            'product_scope_json' => 'array',
            'user_scope_json' => 'array',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Order::class, 'coupon_id');
    }
}
