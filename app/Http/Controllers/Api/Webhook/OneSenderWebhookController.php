<?php

namespace App\Http\Controllers\Api\Webhook;

use App\Http\Controllers\Controller;
use App\Enums\ProviderCategory;
use App\Models\IntegrationConnection;
use App\Models\ProviderWebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OneSenderWebhookController extends Controller
{
    public const HEADER_SIGNATURE = 'X-Onesender-Signature';

    public const EVENT_INBOUND_MESSAGE = 'inbound_message';
    public const EVENT_OUTBOUND_STATUS = 'outbound_status';

    public function __invoke(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $headers = $request->headers->all();
        $providedSignature = $request->header(self::HEADER_SIGNATURE, '');
        $eventId = $request->input('event_id') ?? $request->input('id') ?? hash('sha256', $rawPayload . now()->toIso8601String());

        $providerType = 'onesender';
        $integration = IntegrationConnection::query()
            ->where('provider_category', ProviderCategory::WhatsApp->value)
            ->where('provider_type', $providerType)
            ->where('is_active', true)
            ->first();

        $signatureValid = false;
        $providerConnectionId = null;

        if ($integration) {
            $providerConnectionId = $integration->id;
            $secret = null;
            if (Schema::hasColumn('integration_connections', 'webhook_signing_secret') && $integration->webhook_signing_secret) {
                $secret = is_array($integration->webhook_signing_secret)
                    ? ($integration->webhook_signing_secret['secret'] ?? null)
                    : $integration->webhook_signing_secret;
            }

            if (! $secret) {
                $creds = $integration->getCredentials();
                $secret = $creds['webhook_secret'] ?? $creds['signing_secret'] ?? null;
            }

            if ($secret) {
                $expected = hash_hmac('sha256', $rawPayload, $secret);
                $signatureValid = hash_equals($expected, (string) $providedSignature);
            } else {
                $signatureValid = true;
            }
        } else {
            Log::warning('OneSender webhook diterima tapi integration tidak ditemukan untuk provider onesender');
        }

        $eventType = $request->input('event_type') ?? $request->input('event') ?? 'unknown';

        try {
            DB::transaction(function () use (
                $eventId,
                $rawPayload,
                $headers,
                $request,
                $signatureValid,
                $providerConnectionId,
                $eventType,
                $providerType
            ) {
                $exists = ProviderWebhookEvent::query()
                    ->where('provider_type', $providerType)
                    ->where('event_id', $eventId)
                    ->exists();

                if (! $exists) {
                    ProviderWebhookEvent::query()->create([
                        'provider_type' => $providerType,
                        'event_type' => $eventType,
                        'event_id' => $eventId,
                        'occurred_at_provider' => $request->input('timestamp') ? now() : now(),
                        'signature_valid' => $signatureValid,
                        'integration_connection_id' => $providerConnectionId,
                        'headers_json' => $headers,
                        'payload_json' => $request->all(),
                        'raw_payload' => $rawPayload,
                        'request_ip' => $request->ip(),
                        'outcome' => 'received',
                        'outcome_message' => null,
                        'processed_job_id' => null,
                        'processed_at' => null,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error('OneSender webhook DB error: ' . $e->getMessage());
        }

        if (! $signatureValid && $integration) {
            return response()->json([
                'status' => 'signature_invalid',
                'message' => 'Signature tidak cocok',
            ], 401);
        }

        if (
            $signatureValid
            && in_array($eventType, [self::EVENT_INBOUND_MESSAGE, self::EVENT_OUTBOUND_STATUS, 'message_status', 'message:incoming'], true)
        ) {
            try {
                $jobClass = $this->resolveJobClass($eventType);
                if ($jobClass && class_exists($jobClass)) {
                    $jobClass::dispatch([
                        'event_id' => $eventId,
                        'payload' => $request->all(),
                    ])->delay(now()->addSeconds(2));
                }
            } catch (\Throwable $e) {
                Log::warning('OneSender gagal dispatch job: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'ok',
            'received_at' => now()->toIso8601String(),
            'event_id' => $eventId,
        ], 200);
    }

    private function resolveJobClass(string $eventType): ?string
    {
        $map = [
            self::EVENT_INBOUND_MESSAGE => \App\Jobs\OneSenderInboundProcessJob::class,
            self::EVENT_OUTBOUND_STATUS => \App\Jobs\OneSenderStatusUpdateJob::class,
            'message_status' => \App\Jobs\OneSenderStatusUpdateJob::class,
            'incoming' => \App\Jobs\OneSenderInboundProcessJob::class,
        ];

        return $map[$eventType] ?? null;
    }
}
