<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProviderCategory;
use App\Http\Controllers\Controller;
use App\Models\IntegrationConnection;
use App\Models\Order;
use App\Models\ProviderWebhookEvent;
use App\Services\Payments\PostPaymentHookCoordinator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaymentGatewayWebhookController extends Controller
{
    public function __construct(
        private readonly PostPaymentHookCoordinator $hookCoordinator,
    ) {}

    public function __invoke(Request $request, string $provider): JsonResponse
    {
        $rawPayload = $request->getContent();
        $headers = $request->headers->all();

        $eventId = $this->extractEventId($provider, $request);
        $eventType = $this->extractEventType($provider, $request);
        $orderNumber = $this->extractOrderNumber($provider, $request);

        $integration = IntegrationConnection::query()
            ->where('provider_category', ProviderCategory::Payment->value)
            ->where('provider_type', $provider)
            ->where('is_active', true)
            ->first();

        $signatureValid = $this->validateSignature($provider, $request, $integration, $rawPayload);

        try {
            DB::transaction(function () use (
                $eventId, $eventType, $provider, $rawPayload, $headers, $request, $signatureValid, $integration
            ) {
                $exists = ProviderWebhookEvent::query()
                    ->where('provider_type', 'payment_'.$provider)
                    ->where('event_id', $eventId)
                    ->exists();

                if (! $exists) {
                    ProviderWebhookEvent::query()->create([
                        'provider_type' => 'payment_'.$provider,
                        'event_type' => $eventType,
                        'event_id' => $eventId,
                        'occurred_at_provider' => now(),
                        'signature_valid' => $signatureValid,
                        'integration_connection_id' => $integration?->id,
                        'headers_json' => $headers,
                        'payload_json' => $request->all(),
                        'raw_payload' => $rawPayload,
                        'request_ip' => $request->ip(),
                        'outcome' => 'received',
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error("Payment gateway webhook DB error ({$provider}): ".$e->getMessage());
        }

        if (! $signatureValid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature',
                'provider' => $provider,
            ], 401);
        }

        $paidStatuses = ['paid', 'success', 'settlement', 'capture', 'completed'];
        $isPaidEvent = in_array(strtolower($eventType), $paidStatuses, true);

        if ($isPaidEvent && $orderNumber) {
            $this->processPaidEvent($provider, $orderNumber, $request);
        }

        return response()->json([
            'status' => 'ok',
            'provider' => $provider,
            'event_type' => $eventType,
            'received_at' => now()->toIso8601String(),
        ], 200);
    }

    private function extractEventId(string $provider, Request $request): string
    {
        return match (strtolower($provider)) {
            'xendit' => $request->input('id') ?? $request->input('event_id') ?? 'x-'.Str::uuid()->toString(),
            'midtrans' => $request->input('transaction_id') ?? $request->input('order_id') ?? 'm-'.Str::uuid()->toString(),
            'finpay' => $request->input('trx_id') ?? $request->input('reference') ?? 'f-'.Str::uuid()->toString(),
            default => hash('sha256', $request->getContent().now()->toIso8601String()),
        };
    }

    private function extractEventType(string $provider, Request $request): string
    {
        return match (strtolower($provider)) {
            'xendit' => $request->header('x-callback-event', $request->input('status') ?? 'unknown'),
            'midtrans' => $request->input('transaction_status') ?? 'unknown',
            'finpay' => $request->input('status') ?? 'unknown',
            default => $request->input('event_type') ?? $request->input('status') ?? 'unknown',
        };
    }

    private function extractOrderNumber(string $provider, Request $request): ?string
    {
        return match (strtolower($provider)) {
            'xendit' => $request->input('external_id'),
            'midtrans' => $request->input('order_id'),
            'finpay' => $request->input('merchant_trade_no') ?? $request->input('order_no'),
            default => $request->input('order_number') ?? $request->input('order_id'),
        };
    }

    private function validateSignature(string $provider, Request $request, ?IntegrationConnection $integration, string $rawPayload): bool
    {
        if (! $integration) {
            return false;
        }

        $creds = $integration->getCredentials();
        $secret = $creds['webhook_secret'] ?? $creds['callback_token'] ?? $creds['secret_key'] ?? null;

        if (Schema::hasColumn('integration_connections', 'webhook_signing_secret') && $integration->webhook_signing_secret) {
            $wsSecret = is_array($integration->webhook_signing_secret)
                ? ($integration->webhook_signing_secret['secret'] ?? null)
                : $integration->webhook_signing_secret;
            if ($wsSecret) {
                $secret = $wsSecret;
            }
        }

        if (! $secret) {
            return false;
        }

        $provided = match (strtolower($provider)) {
            'xendit' => $request->header('x-callback-token', ''),
            'midtrans' => $request->input('signature_key') ?? '',
            'finpay' => $request->header('X-Signature', ''),
            default => $request->header('X-Signature') ?? $request->input('signature') ?? '',
        };

        if (strtolower($provider) === 'xendit') {
            return $provided === $secret;
        }

        if (strtolower($provider) === 'midtrans') {
            $orderId = $request->input('order_id');
            $statusCode = $request->input('status_code');
            $grossAmount = $request->input('gross_amount');
            $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$secret);

            return hash_equals(strtolower($expected), strtolower($provided));
        }

        if ($provided) {
            $expected = hash_hmac('sha256', $rawPayload, $secret);

            return hash_equals($expected, $provided);
        }

        return false;
    }

    private function processPaidEvent(string $provider, string $orderNumber, Request $request): void
    {
        try {
            $order = Order::query()->where('number', $orderNumber)->first();
            if (! $order) {
                Log::warning("Payment webhook {$provider}: order {$orderNumber} tidak ditemukan");

                return;
            }

            if ($order->isPaidOrLater()) {
                return;
            }

            DB::transaction(function () use ($order, $request) {
                $order->status = OrderStatus::Paid;
                $order->paid_at = now();
                $order->save();

                $attempt = $order->latestPaymentAttempt();
                if ($attempt) {
                    $attempt->status = PaymentStatus::Verified;
                    $attempt->verified_at = now();
                    $attempt->gateway_transaction_reference = $request->input('transaction_id') ?? $request->input('reference_id') ?? $attempt->gateway_transaction_reference;
                    $attempt->save();
                }
            });

            $this->hookCoordinator->execute($order);
        } catch (\Throwable $e) {
            Log::error("Payment webhook process paid gagal ({$provider}/{$orderNumber}): ".$e->getMessage());
        }
    }
}
