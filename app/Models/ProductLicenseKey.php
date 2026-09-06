<?php

namespace App\Models;

use App\Enums\ProductLicenseKeyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLicenseKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'encrypted_key_value',
        'fingerprint_sha256',
        'status',
        'max_activations',
        'activation_count',
        'assigned_to_user_id',
        'assigned_from_order_item_id',
        'assigned_from_order_id',
        'assigned_at',
        'expires_at',
        'suspended_at',
        'suspension_reason',
        'revoked_at',
        'revocation_reason',
        'import_batch_ref',
        'imported_by_user_id',
        'features_json',
        'product_signing_secret_fingerprint',
    ];

    protected $casts = [
        'status' => ProductLicenseKeyStatus::class,
        'encrypted_key_value' => 'encrypted',
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
        'suspended_at' => 'datetime',
        'revoked_at' => 'datetime',
        'features_json' => 'json',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedFromOrderItem()
    {
        return $this->belongsTo(OrderItem::class, 'assigned_from_order_item_id');
    }

    public function assignedFromOrder()
    {
        return $this->belongsTo(Order::class, 'assigned_from_order_id');
    }

    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by_user_id');
    }

    public function activations()
    {
        return $this->hasMany(ProductLicenseActivation::class);
    }

    public function validationLogs()
    {
        return $this->hasMany(ProductLicenseValidationLog::class);
    }
}
