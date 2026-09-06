<?php

namespace App\Models;

use App\Enums\LicenseTier;
use App\Enums\LicenseStatus;
use Illuminate\Database\Eloquent\Model;

class ApplicationLicense extends Model
{
    protected $fillable = [
        'installation_uuid',
        'license_key_sha256',
        'domain_name',
        'customer_name',
        'customer_email',
        'tier',
        'seats_allowed',
        'max_branches',
        'activated_at',
        'expires_at',
        'last_validated_at',
        'validation_signed_lease_json',
        'lease_valid_until',
        'status',
        'suspension_reason',
        'revocation_reason',
    ];

    protected function casts(): array
    {
        return [
            'tier' => LicenseTier::class,
            'status' => LicenseStatus::class,
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_validated_at' => 'datetime',
            'validation_signed_lease_json' => 'array',
            'lease_valid_until' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === LicenseStatus::Active;
    }

    public function hasExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }
}
