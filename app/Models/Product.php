<?php

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'type',
        'name',
        'slug',
        'status',
        'visibility',
        'price',
        'commission_eligible',
        'points_eligible',
        'points_policy_json',
        'description',
        'meta_json',
        'package_code',
        'include_scanner',
        'include_wsl_license_access',
        'include_id_card_license',
        'stifin_test_type_code',
        'wsl_level',
        'landing_page_slug',
        'is_published',
        'published_at',
        'is_catalog_visible',
        'sort_order',
        'featured_image_path',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'status' => ProductStatus::class,
            'price' => 'decimal:2',
            'commission_eligible' => 'boolean',
            'points_eligible' => 'boolean',
            'points_policy_json' => 'array',
            'meta_json' => 'array',
            'include_scanner' => 'boolean',
            'include_wsl_license_access' => 'boolean',
            'include_id_card_license' => 'boolean',
            'wsl_level' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'is_catalog_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function voucherConfig(): HasOne
    {
        return $this->hasOne(VoucherProductConfig::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function orderBumps(): HasMany
    {
        return $this->hasMany(OrderBump::class, 'primary_product_id');
    }

    public function isVoucher(): bool
    {
        return $this->type === ProductType::Voucher;
    }

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    public function scopeVoucher($query)
    {
        return $query->where('type', ProductType::Voucher);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProductStatus::Active);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
