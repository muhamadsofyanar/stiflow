<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherProductConfig extends Model
{
    protected $fillable = [
        'product_id',
        'unit_price',
        'min_qty',
        'max_qty',
        'presets_json',
        'free_units_rule_json',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'min_qty' => 'integer',
            'max_qty' => 'integer',
            'presets_json' => 'array',
            'free_units_rule_json' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function presets(): array
    {
        return $this->presets_json ?? [1, 5, 10];
    }
}
