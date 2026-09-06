<?php

namespace App\Services\Payments;

use App\Enums\PaymentGatewayProvider;
use App\Integrations\Payments\PaymentGatewayContract;
use App\Integrations\Payments\XenditPaymentAdapter;
use App\Integrations\Payments\MidtransPaymentAdapter;
use App\Integrations\Payments\FinpayPaymentAdapter;
use App\Models\PaymentGatewayConfig;
use InvalidArgumentException;

class PaymentGatewayRoutingService
{
    public function resolve(string|PaymentGatewayProvider $provider): PaymentGatewayContract
    {
        $value = is_string($provider) ? $provider : $provider->value;

        return match ($value) {
            PaymentGatewayProvider::Xendit->value => app(XenditPaymentAdapter::class),
            PaymentGatewayProvider::Midtrans->value => app(MidtransPaymentAdapter::class),
            PaymentGatewayProvider::Finpay->value => app(FinpayPaymentAdapter::class),
            default => throw new InvalidArgumentException("Provider {$value} tidak didukung."),
        };
    }

    public function listAvailableForCheckout(float $amount = 0): array
    {
        $configs = PaymentGatewayConfig::query()->active()->get();

        $result = [];
        foreach ($configs as $cfg) {
            if ($cfg->minimum_amount > 0 && $amount < $cfg->minimum_amount) {
                continue;
            }
            if ($cfg->maximum_amount > 0 && $amount > $cfg->maximum_amount) {
                continue;
            }

            $fee = $this->calculateFee($cfg, $amount);

            $result[] = [
                'id' => $cfg->id,
                'provider' => $cfg->provider->value,
                'display_name' => $cfg->display_name,
                'sort_order' => $cfg->sort_order,
                'fee_fixed' => (float) $cfg->fixed_fee,
                'fee_percent' => (float) $cfg->percent_fee,
                'fee_total' => $fee,
                'net_amount' => $amount - $fee,
                'minimum_amount' => (float) $cfg->minimum_amount,
                'maximum_amount' => (float) $cfg->maximum_amount,
                'instructions_markdown' => $cfg->instructions_markdown,
            ];
        }

        usort($result, fn ($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        return $result;
    }

    public function calculateFee(PaymentGatewayConfig $config, float $amount): float
    {
        $fixed = (float) $config->fixed_fee;
        $pct = ((float) $config->percent_fee) / 100.0;

        return $fixed + ($amount * $pct);
    }
}
