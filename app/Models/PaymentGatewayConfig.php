<?php

namespace App\Models;

use App\Enums\PaymentGatewayProvider;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayConfig extends Model
{
    protected $fillable = [
        'provider',
        'display_name',
        'is_active',
        'sort_order',
        'credentials_json',
        'webhook_url',
        'supported_currencies_json',
        'minimum_amount',
        'maximum_amount',
        'fixed_fee',
        'percent_fee',
        'instructions_markdown',
        'meta_json',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentGatewayProvider::class,
            'is_active' => 'boolean',
            'credentials_json' => 'array',
            'supported_currencies_json' => 'array',
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'fixed_fee' => 'decimal:2',
            'percent_fee' => 'decimal:2',
            'meta_json' => 'array',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
