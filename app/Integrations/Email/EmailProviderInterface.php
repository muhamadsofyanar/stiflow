<?php

namespace App\Integrations\Email;

interface EmailProviderInterface
{
    public function testConnection(array $credentials): array;

    public function sendTransactional(string $to, string $subject, string $body, array $options = []): array;

    public function sendBatch(array $recipients, string $subject, string $bodyTemplate, array $options = []): array;

    public function parseWebhook(array $payload, array $headers = []): array;
}
