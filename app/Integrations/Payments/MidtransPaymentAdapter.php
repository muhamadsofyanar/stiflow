<?php

namespace App\Integrations\Payments;

use App\Exceptions\ProviderNotConfigured;

class MidtransPaymentAdapter implements PaymentGatewayContract
{
    public function createCharge(array $payload): array
    {
        throw ProviderNotConfigured::for('Midtrans');
    }

    public function checkStatus(string $transactionId): array
    {
        throw ProviderNotConfigured::for('Midtrans');
    }

    public function parseWebhook(array $headers, string $rawBody): array
    {
        throw ProviderNotConfigured::for('Midtrans');
    }

    public function refund(string $transactionId, float $amount, string $reason = ''): array
    {
        throw ProviderNotConfigured::for('Midtrans');
    }

    public function cancel(string $transactionId): array
    {
        throw ProviderNotConfigured::for('Midtrans');
    }
}
