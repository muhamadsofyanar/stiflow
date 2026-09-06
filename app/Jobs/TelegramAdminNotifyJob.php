<?php

namespace App\Jobs;

use App\Integrations\Telegram\TelegramAdminNotificationAdapter;
use App\Models\IntegrationConnection;
use App\Models\Payout;
use App\Models\VoucherFulfillment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramAdminNotifyJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public int $backoff = 60;

    public const TYPE_ALERT = 'alert';
    public const TYPE_PAYOUT_REQUEST = 'payout_request';
    public const TYPE_VOUCHER_REVIEW = 'voucher_review';
    public const TYPE_PROVIDER_DEGRADED = 'provider_degraded';

    public function __construct(
        public readonly string $type,
        public readonly array $payload = [],
    ) {
    }

    public static function alert(string $title, string $body, string $level = 'warning', array $extra = []): self
    {
        return new self(self::TYPE_ALERT, [
            'title' => $title,
            'body' => $body,
            'level' => $level,
            'extra' => $extra,
        ]);
    }

    public static function payoutRequest(Payout $payout): self
    {
        return new self(self::TYPE_PAYOUT_REQUEST, [
            'payout_id' => $payout->id,
        ]);
    }

    public static function voucherReview(VoucherFulfillment $voucher): self
    {
        return new self(self::TYPE_VOUCHER_REVIEW, [
            'voucher_id' => $voucher->id,
        ]);
    }

    public static function providerDegraded(IntegrationConnection $connection, string $errorMessage = ''): self
    {
        return new self(self::TYPE_PROVIDER_DEGRADED, [
            'connection_id' => $connection->id,
            'error_message' => $errorMessage,
        ]);
    }

    public function handle(TelegramAdminNotificationAdapter $adapter): void
    {
        try {
            $sent = match ($this->type) {
                self::TYPE_ALERT => $adapter->sendAlert(
                    title: (string) ($this->payload['title'] ?? 'System Alert'),
                    body: (string) ($this->payload['body'] ?? ''),
                    level: (string) ($this->payload['level'] ?? 'warning'),
                    extra: (array) ($this->payload['extra'] ?? []),
                ),
                self::TYPE_PAYOUT_REQUEST => $this->handlePayout($adapter),
                self::TYPE_VOUCHER_REVIEW => $this->handleVoucher($adapter),
                self::TYPE_PROVIDER_DEGRADED => $this->handleProvider($adapter),
                default => false,
            };

            if (! $sent && $adapter->isConfigured()) {
                Log::warning('TelegramAdminNotifyJob tidak terkirim untuk type=' . $this->type);
            }
        } catch (\Throwable $e) {
            Log::error('TelegramAdminNotifyJob exception: ' . $e->getMessage());
            throw $e;
        }
    }

    private function handlePayout(TelegramAdminNotificationAdapter $adapter): bool
    {
        $payoutId = (int) ($this->payload['payout_id'] ?? 0);
        $payout = Payout::query()->find($payoutId);
        if (! $payout) {
            Log::warning("Telegram payout notify: payout {$payoutId} tidak ditemukan");
            return false;
        }

        return $adapter->sendPayoutRequest($payout);
    }

    private function handleVoucher(TelegramAdminNotificationAdapter $adapter): bool
    {
        $voucherId = (int) ($this->payload['voucher_id'] ?? 0);
        $voucher = VoucherFulfillment::query()->find($voucherId);
        if (! $voucher) {
            return false;
        }

        return $adapter->sendNeedsReviewVoucher($voucher);
    }

    private function handleProvider(TelegramAdminNotificationAdapter $adapter): bool
    {
        $connId = (int) ($this->payload['connection_id'] ?? 0);
        $connection = IntegrationConnection::query()->find($connId);
        if (! $connection) {
            return false;
        }

        return $adapter->sendProviderDegraded(
            $connection,
            (string) ($this->payload['error_message'] ?? '')
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('TelegramAdminNotifyJob FAILED: ' . $exception->getMessage(), [
            'type' => $this->type,
            'payload' => $this->payload,
        ]);
    }
}
