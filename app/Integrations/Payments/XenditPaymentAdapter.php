<?php

namespace App\Integrations\Payments;

use App\Exceptions\ProviderNotConfigured;

class XenditPaymentAdapter implements PaymentGatewayContract
{
    public function createCharge(array $payload): array
    {
        throw ProviderNotConfigured::for('Xendit');
    }

    public function checkStatus(string $transactionId): array
    {
        throw ProviderNotConfigured::for('Xendit');
    }

    public function parseWebhook(array $headers, string $rawBody): array
    {
        throw ProviderNotConfigured::for('Xendit');
    }

    public function refund(string $transactionId, float $amount, string $reason = ''): array
    {
        throw ProviderNotConfigured::for('Xendit');
    }

    public function cancel(string $transactionId): array
    {
        throw ProviderNotConfigured::for('Xendit');
    }
}
