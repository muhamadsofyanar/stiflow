<?php

namespace App\Models;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\ProviderCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_category',
        'provider_type',
        'provider_name',
        'display_name',
        'encrypted_credentials',
        'config_json',
        'status',
        'last_error_message',
        'last_tested_at',
        'last_success_at',
        'degraded_since_at',
        'disabled_until_at',
        'disabled_reason',
        'is_active',
        'is_primary',
        'failure_count',
        'circuit_breaker_threshold',
        'rate_limit_per_minute',
        'owned_by_user_id',
        'allowed_server_ip',
        'webhook_secret_fingerprint',
        'health_check_last_result_json',
    ];

    protected $casts = [
        'provider_category' => ProviderCategory::class,
        'status' => IntegrationConnectionStatus::class,
        'encrypted_credentials' => 'encrypted:array',
        'config_json' => 'json',
        'last_tested_at' => 'datetime',
        'last_success_at' => 'datetime',
        'degraded_since_at' => 'datetime',
        'disabled_until_at' => 'datetime',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'health_check_last_result_json' => 'json',
    ];

    public function getCredentials(): array
    {
        $creds = $this->encrypted_credentials;

        if (is_string($creds)) {
            try {
                $decoded = json_decode($creds, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    return $decoded;
                }
            } catch (\Throwable) {
                // fallthrough
            }

            return [
                'token' => $creds,
            ];
        }

        if (is_array($creds)) {
            return $creds;
        }

        return [];
    }

    public function getCredentialMasked(string $key): ?string
    {
        $value = $this->getCredentials()[$key] ?? null;
        if (null === $value || '' === $value) {
            return null;
        }
        $len = mb_strlen($value);
        if ($len <= 4) {
            return '****';
        }

        return mb_substr($value, 0, 2).'****'.mb_substr($value, -2);
    }

    public function ownedBy()
    {
        return $this->belongsTo(User::class, 'owned_by_user_id');
    }

    public function campaignsAsSender()
    {
        return $this->hasMany(Campaign::class, 'sender_integration_connection_id');
    }

    public function messageDeliveries()
    {
        return $this->hasMany(MessageDelivery::class);
    }
}
