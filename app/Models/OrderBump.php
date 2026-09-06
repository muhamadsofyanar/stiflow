<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBump extends Model
{
    use HasFactory;

    protected $fillable = [
        'primary_product_id',
        'bump_product_id',
        'label',
        'description',
        'discount_price',
        'discount_percent',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function primaryProduct()
    {
        return $this->belongsTo(Product::class, 'primary_product_id');
    }

    public function bumpProduct()
    {
        return $this->belongsTo(Product::class, 'bump_product_id');
    }
}
