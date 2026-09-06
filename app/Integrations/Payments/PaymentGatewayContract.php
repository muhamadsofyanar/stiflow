<?php

namespace App\Integrations\Payments;

interface PaymentGatewayContract
{
    public function createCharge(array $payload): array;

    public function checkStatus(string $transactionId): array;

    public function parseWebhook(array $headers, string $rawBody): array;

    public function refund(string $transactionId, float $amount, string $reason = ''): array;

    public function cancel(string $transactionId): array;
}
