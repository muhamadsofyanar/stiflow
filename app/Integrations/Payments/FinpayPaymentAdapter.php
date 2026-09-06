<?php

namespace App\Integrations\Payments;

use App\Exceptions\ProviderNotConfigured;

class FinpayPaymentAdapter implements PaymentGatewayContract
{
    public function createCharge(array $payload): array
    {
        throw ProviderNotConfigured::for('Finpay');
    }

    public function checkStatus(string $transactionId): array
    {
        throw ProviderNotConfigured::for('Finpay');
    }

    public function parseWebhook(array $headers, string $rawBody): array
    {
        throw ProviderNotConfigured::for('Finpay');
    }

    public function refund(string $transactionId, float $amount, string $reason = ''): array
    {
        throw ProviderNotConfigured::for('Finpay');
    }

    public function cancel(string $transactionId): array
    {
        throw ProviderNotConfigured::for('Finpay');
    }
}
