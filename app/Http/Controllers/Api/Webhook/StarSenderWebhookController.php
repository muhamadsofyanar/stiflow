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

class StarSenderWebhookController extends Controller
{
    public const HEADER_TOKEN = 'Authorization';
    public const HEADER_SIGNATURE = 'X-Starsender-Signature';

    public function __invoke(Request $request, string $provider = 'starsender'): JsonResponse
    {
        $rawPayload = $request->getContent();
        $headers = $request->headers->all();

        $token = $request->bearerToken();
        $signature = $request->header(self::HEADER_SIGNATURE, '');
        $eventId = $request->input('event_id') ?? $request->input('eventId') ?? $request->input('id') ?? hash('sha256', $rawPayload . now()->toIso8601String());

        $integration = IntegrationConnection::query()
            ->where('provider_category', ProviderCategory::WhatsApp->value)
            ->where('provider_type', $provider)
            ->where('is_active', true)
            ->first();

        $tokenValid = true;
        $signatureValid = true;

        if ($integration) {
            $creds = $integration->getCredentials();
            $expectedToken = $creds['bearer_token'] ?? $creds['token'] ?? $creds['api_key'] ?? null;
            if ($expectedToken && $token !== $expectedToken) {
                $tokenValid = false;
            }

            $signingSecret = $creds['webhook_secret'] ?? $creds['signing_secret'] ?? null;
            if (Schema::hasColumn('integration_connections', 'webhook_signing_secret') && $integration->webhook_signing_secret) {
                $wsSecret = is_array($integration->webhook_signing_secret)
                    ? ($integration->webhook_signing_secret['secret'] ?? null)
                    : $integration->webhook_signing_secret;
                if ($wsSecret) {
                    $signingSecret = $wsSecret;
                }
            }

            if ($signingSecret && $signature) {
                $computed = hash_hmac('sha256', $rawPayload, $signingSecret);
                $signatureValid = hash_equals($computed, $signature);
            }
        } else {
            Log::warning("StarSender webhook: integration tidak ditemukan untuk provider={$provider}");
        }

        $eventType = $request->input('event_type') ?? $request->input('event') ?? $request->input('type') ?? 'unknown';

        try {
            DB::transaction(function () use (
                $eventId, $provider, $rawPayload, $headers, $request, $signatureValid, $tokenValid, $integration, $eventType
            ) {
                $exists = ProviderWebhookEvent::query()
                    ->where('provider_type', $provider)
                    ->where('event_id', $eventId)
                    ->exists();

                if (! $exists) {
                    ProviderWebhookEvent::query()->create([
                        'provider_type' => $provider,
                        'event_type' => $eventType,
                        'event_id' => $eventId,
                        'occurred_at_provider' => $request->input('timestamp') ?? now(),
                        'signature_valid' => $tokenValid && $signatureValid,
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
            Log::error('StarSender webhook DB error: ' . $e->getMessage());
        }

        if (! $tokenValid) {
            return response()->json(['status' => 'error', 'message' => 'Token tidak valid'], 401);
        }

        if (! $signatureValid) {
            return response()->json(['status' => 'error', 'message' => 'Signature tidak cocok'], 401);
        }

        return response()->json([
            'status' => 'ok',
            'received_at' => now()->toIso8601String(),
            'event_id' => $eventId,
        ], 200);
    }
}
