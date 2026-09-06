<?php

namespace App\Integrations\Whatsapp;

interface WhatsappProviderInterface
{
    public function testConnection(array $credentials): array;

    public function sendText(string $to, string $message, array $options = []): array;

    public function sendMedia(string $to, string $mediaUrl, string $mediaType, string $caption = '', array $options = []): array;

    public function parseWebhook(array $payload, array $headers = []): array;

    public function getDeviceHealth(array $credentials): array;
}
