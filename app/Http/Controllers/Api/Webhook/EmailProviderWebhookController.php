<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Enums\DeliveryOutcome;
use App\Enums\ProviderCategory;
use App\Models\IntegrationConnection;
use App\Models\MessageDelivery;
use App\Models\ProviderWebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EmailProviderWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider): JsonResponse
    {
        $rawPayload = $request->getContent();
        $headers = $request->headers->all();
        $eventId = $request->input('event_id') ?? hash('sha256', $rawPayload . now()->toIso8601String());
        $eventType = $request->input('event') ?? $request->input('type') ?? $request->input('event_type') ?? 'unknown';

        $integration = IntegrationConnection::query()
            ->where('provider_category', ProviderCategory::Email->value)
            ->where(function ($q) use ($provider) {
                $q->where('provider_type', $provider);
            })
            ->where('is_active', true)
            ->first();

        $signatureValid = true;
        if (! $integration) {
            Log::warning("Email webhook: integration email {$provider} tidak ditemukan");
        } else {
            $signatureValid = $this->validateEmailSignature($provider, $request, $integration, $rawPayload);
        }

        $outcome = $this->mapOutcome($provider, $eventType, $request);

        try {
            DB::transaction(function () use (
                $eventId,
                $eventType,
                $provider,
                $rawPayload,
                $headers,
                $request,
                $signatureValid,
                $integration,
                $outcome
            ) {
                $exists = ProviderWebhookEvent::query()
                    ->where('provider_type', 'email_' . $provider)
                    ->where('event_id', $eventId)
                    ->exists();

                if (! $exists) {
                    ProviderWebhookEvent::query()->create([
                        'provider_type' => 'email_' . $provider,
                        'event_type' => $eventType,
                        'event_id' => $eventId,
                        'occurred_at_provider' => now(),
                        'signature_valid' => $signatureValid,
                        'integration_connection_id' => $integration?->id,
                        'headers_json' => $headers,
                        'payload_json' => $request->all(),
                        'raw_payload' => $rawPayload,
                        'request_ip' => $request->ip(),
                        'outcome' => $outcome,
                    ]);
                }

                $deliveryId = $request->input('delivery_id')
                    ?? $request->input('message_id')
                    ?? $request->input('sg_message_id')
                    ?? $request->input('MessageID')
                    ?? null;

                if ($deliveryId) {
                    $delivery = MessageDelivery::query()
                        ->where('provider_reference', $deliveryId)
                        ->orWhere('provider_message_id', $deliveryId)
                        ->first();

                    if ($delivery && $outcome) {
                        $delivery->delivery_outcome = $outcome;
                        $delivery->last_status_event = $eventType;
                        $delivery->provider_event_payload_json = array_merge(
                            (array) $delivery->provider_event_payload_json,
                            [$eventType => $request->all()]
                        );
                        if (! $delivery->delivered_at && in_array($outcome, [
                            DeliveryOutcome::Delivered->value,
                            DeliveryOutcome::Opened->value,
                            DeliveryOutcome::Clicked->value,
                        ], true)) {
                            $delivery->delivered_at = now();
                        }
                        if (! $delivery->failed_at && in_array($outcome, [
                            DeliveryOutcome::Bounced->value,
                            DeliveryOutcome::Complaint->value,
                            DeliveryOutcome::Failed->value,
                        ], true)) {
                            $delivery->failed_at = now();
                        }
                        $delivery->save();
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Email webhook DB error for provider ' . $provider . ': ' . $e->getMessage());
        }

        if (! $signatureValid) {
            return response()->json(['status' => 'signature_invalid'], 401);
        }

        return response()->json([
            'status' => 'ok',
            'provider' => $provider,
            'event_type' => $eventType,
            'outcome' => $outcome,
            'received_at' => now()->toIso8601String(),
        ], 200);
    }

    private function validateEmailSignature(string $provider, Request $request, IntegrationConnection $integration, string $rawPayload): bool
    {
        $creds = $integration->getCredentials();
        $secret = $creds['webhook_secret'] ?? $creds['signing_secret'] ?? null;

        if (Schema::hasColumn('integration_connections', 'webhook_signing_secret') && $integration->webhook_signing_secret) {
            $wsSecret = is_array($integration->webhook_signing_secret)
                ? ($integration->webhook_signing_secret['secret'] ?? null)
                : $integration->webhook_signing_secret;
            if ($wsSecret) {
                $secret = $wsSecret;
            }
        }

        if (! $secret) {
            return true;
        }

        switch (strtolower($provider)) {
            case 'sendgrid':
                $timestamp = $request->header('X-Twilio-Email-Event-Webhook-Signature', '');
                return $timestamp ? true : true;
            case 'mailgun':
                $token = $request->input('signature.token');
                $timestamp = $request->input('timestamp');
                $signature = $request->input('signature.signature', '');
                if ($token && $timestamp && $signature) {
                    $expected = hash_hmac('sha256', $timestamp . $token, $secret);
                    return hash_equals($expected, $signature);
                }
                return true;
            case 'postmark':
            default:
                return true;
        }
    }

    private function mapOutcome(string $provider, string $eventType, Request $request): ?string
    {
        $lower = strtolower($eventType);

        return match (true) {
            str_contains($lower, 'deliver'), $lower === 'delivered', $lower === 'delivery' => DeliveryOutcome::Delivered->value,
            str_contains($lower, 'bounce'), $lower === 'bounced' => DeliveryOutcome::Bounced->value,
            str_contains($lower, 'complaint'), $lower === 'spamreport', $lower === 'spam' => DeliveryOutcome::Complaint->value,
            str_contains($lower, 'open') => DeliveryOutcome::Opened->value,
            str_contains($lower, 'click') => DeliveryOutcome::Clicked->value,
            str_contains($lower, 'fail'), str_contains($lower, 'drop'), $lower === 'dropped' => DeliveryOutcome::Failed->value,
            str_contains($lower, 'reply') => DeliveryOutcome::Replied->value,
            default => null,
        };
    }
}
