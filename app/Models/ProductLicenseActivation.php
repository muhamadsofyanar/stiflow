<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLicenseActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_license_key_id',
        'device_fingerprint_sha256',
        'instance_label',
        'ip_address_first_seen',
        'hardware_info_json',
        'hostname',
        'os_name',
        'app_version',
        'activation_count',
        'is_active',
        'first_activated_at',
        'last_heartbeat_at',
        'deactivated_at',
        'deactivation_reason',
    ];

    protected $casts = [
        'hardware_info_json' => 'json',
        'is_active' => 'boolean',
        'first_activated_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'deactivated_at' => 'datetime',
    ];

    public function productLicenseKey()
    {
        return $this->belongsTo(ProductLicenseKey::class);
    }
}
