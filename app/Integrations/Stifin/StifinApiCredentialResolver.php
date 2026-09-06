<?php

namespace App\Integrations\Stifin;

use App\Enums\IntegrationConnectionStatus;
use App\Enums\ProviderCategory;
use App\Models\IntegrationConnection;

class StifinApiCredentialResolver
{
    public function resolvePrimaryCredentials(): array
    {
        $conn = IntegrationConnection::query()
            ->where('provider_category', ProviderCategory::StifinApi)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->where('status', IntegrationConnectionStatus::Active)
            ->orderBy('id', 'desc')
            ->first();

        if (! $conn) {
            return $this->fallbackFromEnv();
        }

        $creds = $conn->getCredentials();

        $baseUrl = $creds['base_url'] ?? $creds['endpoint'] ?? null;
        $userId = $creds['user_id'] ?? $creds['userid'] ?? $creds['api_user'] ?? null;
        $authHeader = $creds['auth_header'] ?? null;
        $authValue = $creds['auth_value'] ?? null;
        $connectTimeout = isset($creds['connect_timeout']) ? (int) $creds['connect_timeout'] : null;
        $totalTimeout = isset($creds['timeout']) ? (int) $creds['timeout'] : (isset($creds['total_timeout']) ? (int) $creds['total_timeout'] : null);

        return [
            'base_url' => $baseUrl ?? config('services.stifin.base_url', 'https://apro.stifin.id/api'),
            'user_id_identifier' => $userId ?? config('services.stifin.user_id', 'STIFLOW-SYSTEM'),
            'auth_header' => $authHeader ?? config('services.stifin.auth_header'),
            'auth_value' => $authValue ?? config('services.stifin.auth_value'),
            'connect_timeout' => $connectTimeout ?? (int) config('services.stifin.connect_timeout', 15),
            'total_timeout' => $totalTimeout ?? (int) config('services.stifin.timeout', 30),
            'integration_connection_id' => $conn->id,
            'source' => 'db_integration_connection',
        ];
    }

    private function fallbackFromEnv(): array
    {
        return [
            'base_url' => config('services.stifin.base_url', 'https://apro.stifin.id/api'),
            'user_id_identifier' => config('services.stifin.user_id', 'STIFLOW-SYSTEM'),
            'auth_header' => config('services.stifin.auth_header'),
            'auth_value' => config('services.stifin.auth_value'),
            'connect_timeout' => (int) config('services.stifin.connect_timeout', 15),
            'total_timeout' => (int) config('services.stifin.timeout', 30),
            'integration_connection_id' => null,
            'source' => 'env',
        ];
    }
}
