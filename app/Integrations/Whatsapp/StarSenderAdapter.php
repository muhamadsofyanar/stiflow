<?php

namespace App\Integrations\Whatsapp;

use Illuminate\Support\Facades\Http;

class StarSenderAdapter implements WhatsappProviderInterface
{
    private ?string $baseUrl;

    private ?string $token;

    private ?string $deviceKey;

    public function __construct(?string $baseUrl = null, ?string $token = null, ?string $deviceKey = null)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
        $this->deviceKey = $deviceKey;
    }

    public function withCredentials(array $credentials): self
    {
        $clone = clone $this;
        $clone->baseUrl = $credentials['base_url'] ?? $credentials['endpoint'] ?? $this->baseUrl;
        $clone->token = $credentials['token'] ?? $credentials['api_key'] ?? $credentials['secret'] ?? $this->token;
        $clone->deviceKey = $credentials['device_key'] ?? $credentials['device_id'] ?? $credentials['sender_id'] ?? $this->deviceKey;

        return $clone;
    }

    public function testConnection(array $credentials): array
    {
        $adapter = $this->withCredentials($credentials);

        try {
            $health = $adapter->getDeviceHealth($credentials);

            return [
                'success' => ($health['online'] ?? false) || ($health['connected'] ?? false),
                'raw' => $health,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendText(string $to, string $message, array $options = []): array
    {
        $url = rtrim($this->baseUrl ?? '', '/') . '/chat/send';

        $payload = [
            'token' => $this->token,
            'device' => $this->deviceKey,
            'target' => $to,
            'message' => $message,
        ];

        try {
            $response = Http::timeout(30)->connectTimeout(10)
                ->withToken($this->token)
                ->post($url, array_merge($payload, $options));

            return [
                'success' => $response->successful(),
                'http_code' => $response->status(),
                'raw' => $response->json() ?? ['body' => $response->body()],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendMedia(string $to, string $mediaUrl, string $mediaType, string $caption = '', array $options = []): array
    {
        $url = rtrim($this->baseUrl ?? '', '/') . '/chat/send-media';

        $payload = [
            'token' => $this->token,
            'device' => $this->deviceKey,
            'target' => $to,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
            'caption' => $caption,
        ];

        try {
            $response = Http::timeout(60)->connectTimeout(10)
                ->withToken($this->token)
                ->post($url, array_merge($payload, $options));

            return [
                'success' => $response->successful(),
                'http_code' => $response->status(),
                'raw' => $response->json() ?? ['body' => $response->body()],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function parseWebhook(array $payload, array $headers = []): array
    {
        $data = $payload['data'] ?? $payload;
        $event = $data['event'] ?? $data['type'] ?? 'unknown';
        $from = $data['from'] ?? $data['sender'] ?? $data['source'] ?? null;
        $messageId = $data['message_id'] ?? $data['id'] ?? null;
        $text = $data['text'] ?? ($data['message']['text'] ?? null);
        $timestamp = $data['timestamp'] ?? $data['created_at'] ?? now()->toIso8601String();

        $signature = $headers['x-starsender-signature'] ?? $headers['X-StarSender-Signature'] ?? null;

        return [
            'provider' => 'starsender',
            'event' => $event,
            'from' => $from,
            'message_id' => $messageId,
            'text' => $text,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'raw' => $payload,
            'headers' => $headers,
        ];
    }

    public function getDeviceHealth(array $credentials): array
    {
        $adapter = $this->withCredentials($credentials);
        $url = rtrim($adapter->baseUrl ?? '', '/') . '/device/status';

        try {
            $response = Http::timeout(15)->connectTimeout(10)
                ->withToken($adapter->token)
                ->get($url, ['device' => $adapter->deviceKey]);

            $body = $response->json() ?? [];
            $status = $body['status'] ?? ($body['data']['status'] ?? null);

            return [
                'online' => in_array($status, ['online', 'active', 'connected'], true),
                'connected' => $response->successful(),
                'quota_left' => $body['quota'] ?? $body['balance'] ?? null,
                'raw' => $body,
            ];
        } catch (\Throwable $e) {
            return [
                'online' => false,
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
