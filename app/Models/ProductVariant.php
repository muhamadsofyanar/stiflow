<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price_override',
        'stock_qty',
        'in_stock',
        'attributes_json',
        'weight_gram',
        'sort_order',
        'is_active',
        'license_activation_limit',
        'license_expiry_days',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'attributes_json' => 'json',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function licenseKeys()
    {
        return $this->hasMany(ProductLicenseKey::class, 'variant_id');
    }
}
