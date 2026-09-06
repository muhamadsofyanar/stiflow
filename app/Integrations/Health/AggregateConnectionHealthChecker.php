<?php

namespace App\Integrations\Health;

use App\Enums\IntegrationConnectionStatus;
use App\Models\IntegrationConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AggregateConnectionHealthChecker
{
    public function checkAll(int $chunkSize = 5): array
    {
        $connections = IntegrationConnection::query()
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        $results = [];
        $total = $connections->count();
        $processed = 0;
        $healthy = 0;
        $failed = 0;

        foreach ($connections->chunk($chunkSize) as $chunk) {
            foreach ($chunk as $conn) {
                $result = $this->checkSingle($conn);
                $results[] = $result;
                $processed++;

                if ($result['success'] ?? false) {
                    $healthy++;
                } else {
                    $failed++;
                }
            }

            if ($processed < $total) {
                usleep(100000);
            }
        }

        Cache::put('health_last_run_at', now(), now()->addHour());
        Cache::put('health_last_summary', [
            'total' => $total,
            'healthy' => $healthy,
            'failed' => $failed,
            'ran_at' => now()->toIso8601String(),
        ], now()->addHour());

        return [
            'total' => $total,
            'healthy' => $healthy,
            'failed' => $failed,
            'results' => $results,
        ];
    }

    public function checkSingle(IntegrationConnection $connection): array
    {
        $start = microtime(true);
        $success = false;
        $message = '';
        $details = [];

        try {
            DB::beginTransaction();

            $conn = IntegrationConnection::query()
                ->where('id', $connection->id)
                ->lockForUpdate()
                ->firstOrFail();

            $conn->last_tested_at = now();
            $conn->health_check_last_result_json = $conn->health_check_last_result_json ?? [];

            $testResult = $this->resolveAndTest($conn);

            $success = $testResult['success'];
            $message = $testResult['message'];
            $details = $testResult['details'] ?? [];

            $latencyMs = (int) round((microtime(true) - $start) * 1000);
            $details['latency_ms'] = $latencyMs;

            if ($success) {
                $conn->status = IntegrationConnectionStatus::Healthy;
                $conn->last_success_at = now();
                $conn->degraded_since_at = null;
                $conn->failure_count = 0;
                $conn->last_error_message = null;
            } else {
                $conn->failure_count = ((int) $conn->failure_count) + 1;
                $conn->last_error_message = mb_substr($message, 0, 255);

                $threshold = (int) ($conn->circuit_breaker_threshold ?: 3);

                if ($conn->failure_count >= $threshold) {
                    $conn->status = IntegrationConnectionStatus::Degraded;
                    if ($conn->degraded_since_at === null) {
                        $conn->degraded_since_at = now();
                    }
                } else {
                    $conn->status = IntegrationConnectionStatus::Error;
                }

                if ($this->shouldNotifyDegraded($conn)) {
                    try {
                        $notifier = app(\App\Integrations\Telegram\TelegramAdminNotificationAdapter::class);
                        if ($notifier->isConfigured()) {
                            $notifier->sendProviderDegraded($conn, $message);
                        }
                    } catch (\Throwable) {
                    }
                }
            }

            $conn->health_check_last_result_json = [
                'ran_at' => now()->toIso8601String(),
                'success' => $success,
                'message' => $message,
                'latency_ms' => $latencyMs,
                'details' => $details,
            ] + (is_array($conn->health_check_last_result_json) ? $conn->health_check_last_result_json : []);

            $conn->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $success = false;
            $message = 'Health check exception: ' . $e->getMessage();
            Log::warning("Health check failed for connection {$connection->id}: " . $e->getMessage());
        }

        return [
            'connection_id' => $connection->id,
            'display_name' => $connection->display_name,
            'provider_type' => $connection->provider_type,
            'success' => $success,
            'message' => $message,
            'details' => $details,
        ];
    }

    private function resolveAndTest(IntegrationConnection $conn): array
    {
        $category = is_object($conn->provider_category) ? $conn->provider_category->value : (string) $conn->provider_category;
        $providerType = strtolower((string) $conn->provider_type);

        switch (true) {
            case $category === 'stifin_api' || $providerType === 'stifin':
                return $this->testStifinApi($conn);
            case $category === 'whatsapp' || str_contains($providerType, 'sender') || str_contains($providerType, 'wa'):
                return $this->testWhatsAppProvider($conn);
            case $category === 'email':
                return $this->testEmailProvider($conn);
            case $category === 'payment':
                return $this->testPaymentProvider($conn);
            case str_contains($category, 'telegram') || $providerType === 'telegram_bot':
                return $this->testTelegramBot($conn);
            default:
                return $this->testGenericPing($conn);
        }
    }

    private function testStifinApi(IntegrationConnection $conn): array
    {
        $creds = $conn->getCredentials();
        $baseUrl = $creds['base_url'] ?? $conn->config_json['base_url'] ?? 'https://api.stifin.com';
        $apiKey = $creds['api_key'] ?? $creds['token'] ?? '';

        if (! $apiKey) {
            return ['success' => false, 'message' => 'API key tidak dikonfigurasi', 'details' => []];
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->get(rtrim($baseUrl, '/') . '/health/ping');

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Stifin API reachable', 'details' => ['http_status' => $response->status()]];
            }

            return ['success' => false, 'message' => 'HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 200), 'details' => []];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'details' => []];
        }
    }

    private function testWhatsAppProvider(IntegrationConnection $conn): array
    {
        $creds = $conn->getCredentials();
        $baseUrl = $creds['base_url'] ?? $conn->config_json['base_url'] ?? null;

        if (! $baseUrl) {
            return ['success' => true, 'message' => 'Base URL tidak ada, skip HTTP check (passive OK)', 'details' => ['passive' => true]];
        }

        try {
            $response = Http::timeout(6)->get(rtrim($baseUrl, '/') . '/health');
            return [
                'success' => $response->successful() || $response->status() === 401 || $response->status() === 403,
                'message' => 'WA provider responded HTTP ' . $response->status(),
                'details' => ['http_status' => $response->status()],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'details' => []];
        }
    }

    private function testEmailProvider(IntegrationConnection $conn): array
    {
        $creds = $conn->getCredentials();
        $apiKey = $creds['api_key'] ?? $creds['token'] ?? '';

        if (! $apiKey) {
            return ['success' => false, 'message' => 'Email API key tidak ada'];
        }

        return ['success' => true, 'message' => 'Email provider credential check OK (no test call performed)', 'details' => ['passive' => true]];
    }

    private function testPaymentProvider(IntegrationConnection $conn): array
    {
        $creds = $conn->getCredentials();
        $secret = $creds['secret_key'] ?? $creds['api_key'] ?? '';

        if (! $secret) {
            return ['success' => false, 'message' => 'Payment secret key tidak dikonfigurasi'];
        }

        return ['success' => true, 'message' => 'Payment credential check OK', 'details' => ['passive' => true]];
    }

    private function testTelegramBot(IntegrationConnection $conn): array
    {
        $creds = $conn->getCredentials();
        $botToken = $creds['bot_token'] ?? $creds['token'] ?? '';

        if (! $botToken) {
            return ['success' => false, 'message' => 'Telegram bot token tidak ada'];
        }

        try {
            $response = Http::timeout(8)->get("https://api.telegram.org/bot{$botToken}/getMe");
            if ($response->successful() && $response->json('ok') === true) {
                $username = $response->json('result.username') ?? 'unknown';
                return ['success' => true, 'message' => "Bot {$username} OK", 'details' => ['username' => $username]];
            }
            return ['success' => false, 'message' => 'Telegram API: ' . mb_substr($response->body(), 0, 200)];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function testGenericPing(IntegrationConnection $conn): array
    {
        $creds = $conn->getCredentials();
        $baseUrl = $creds['base_url'] ?? ($conn->config_json['base_url'] ?? null);

        if (! $baseUrl) {
            return ['success' => true, 'message' => 'Connection configured (no endpoint untuk di-test)', 'details' => ['passive' => true]];
        }

        try {
            $response = Http::timeout(5)->head($baseUrl);
            return [
                'success' => $response->successful() || $response->status() === 405 || $response->status() === 401,
                'message' => 'HTTP ' . $response->status(),
                'details' => ['http_status' => $response->status()],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function shouldNotifyDegraded(IntegrationConnection $conn): bool
    {
        $nowDegraded = $conn->status === IntegrationConnectionStatus::Degraded || $conn->status === IntegrationConnectionStatus::Offline;
        if (! $nowDegraded) {
            return false;
        }

        $lastNotified = Cache::get('health_notify_' . $conn->id);
        if ($lastNotified && $lastNotified->gt(now()->subMinutes(60))) {
            return false;
        }

        Cache::put('health_notify_' . $conn->id, now(), now()->addMinutes(60));

        return true;
    }
}
