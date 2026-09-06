<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLicenseValidationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_license_key_id',
        'fingerprint_given',
        'device_fingerprint_given',
        'outcome',
        'rejection_reason',
        'ip_address',
        'user_agent',
        'product_signature_valid',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function productLicenseKey()
    {
        return $this->belongsTo(ProductLicenseKey::class);
    }
}
