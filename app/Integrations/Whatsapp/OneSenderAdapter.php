<?php

namespace App\Integrations\Whatsapp;

use Illuminate\Support\Facades\Http;

class OneSenderAdapter implements WhatsappProviderInterface
{
    private ?string $baseUrl;

    private ?string $apiKey;

    private ?string $deviceId;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null, ?string $deviceId = null)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->deviceId = $deviceId;
    }

    public function withCredentials(array $credentials): self
    {
        $clone = clone $this;
        $clone->baseUrl = $credentials['base_url'] ?? $credentials['host'] ?? $this->baseUrl;
        $clone->apiKey = $credentials['api_key'] ?? $credentials['token'] ?? $this->apiKey;
        $clone->deviceId = $credentials['device_id'] ?? $credentials['sender'] ?? $this->deviceId;

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
        $url = rtrim($this->baseUrl ?? '', '/') . '/send-message';

        $payload = [
            'api_key' => $this->apiKey,
            'sender' => $this->deviceId,
            'number' => $to,
            'message' => $message,
        ];

        try {
            $response = Http::timeout(30)->connectTimeout(10)->post($url, array_merge($payload, $options));

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
        $url = rtrim($this->baseUrl ?? '', '/') . '/send-media';

        $payload = [
            'api_key' => $this->apiKey,
            'sender' => $this->deviceId,
            'number' => $to,
            'url' => $mediaUrl,
            'type' => $mediaType,
            'caption' => $caption,
        ];

        try {
            $response = Http::timeout(60)->connectTimeout(10)->post($url, array_merge($payload, $options));

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
        $event = $payload['event'] ?? $payload['type'] ?? ($payload['data']['event'] ?? 'unknown');
        $from = $payload['from'] ?? $payload['sender'] ?? ($payload['data']['from'] ?? null);
        $messageId = $payload['message_id'] ?? $payload['id'] ?? ($payload['data']['id'] ?? null);
        $text = $payload['text'] ?? $payload['message']['text'] ?? ($payload['data']['text'] ?? null);
        $timestamp = $payload['timestamp'] ?? now()->toIso8601String();

        return [
            'provider' => 'onesender',
            'event' => $event,
            'from' => $from,
            'message_id' => $messageId,
            'text' => $text,
            'timestamp' => $timestamp,
            'raw' => $payload,
            'headers' => $headers,
        ];
    }

    public function getDeviceHealth(array $credentials): array
    {
        $adapter = $this->withCredentials($credentials);
        $url = rtrim($adapter->baseUrl ?? '', '/') . '/device-info';

        try {
            $response = Http::timeout(15)->connectTimeout(10)->post($url, [
                'api_key' => $adapter->apiKey,
                'sender' => $adapter->deviceId,
            ]);

            $body = $response->json() ?? [];

            return [
                'online' => ($body['status'] ?? null) === 'connected' || ($body['is_online'] ?? false) || $response->successful(),
                'battery' => $body['battery'] ?? null,
                'connected' => $response->successful(),
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
