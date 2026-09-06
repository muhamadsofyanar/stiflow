<?php

namespace App\Integrations\Telegram;

use App\Enums\ProviderCategory;
use App\Models\IntegrationConnection;
use App\Models\Payout;
use App\Models\VoucherFulfillment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

interface AdminNotifierContract
{
    public function sendAlert(string $title, string $body, string $level = 'warning', array $extra = []): bool;

    public function sendPayoutRequest(Payout $payout): bool;

    public function sendNeedsReviewVoucher(VoucherFulfillment $voucher): bool;

    public function sendProviderDegraded(IntegrationConnection $connection, string $errorMessage = ''): bool;
}

class TelegramAdminNotificationAdapter implements AdminNotifierContract
{
    private ?string $botToken;

    private ?string $chatId;

    private bool $configured = false;

    public function __construct()
    {
        $connection = IntegrationConnection::query()
            ->where('provider_category', ProviderCategory::WhatsApp->value === 'whatsapp' ? 'telegram' : ProviderCategory::WhatsApp->value)
            ->where(function ($q) {
                $q->where('provider_type', 'telegram_bot')
                    ->orWhere('provider_category', function ($sub) {
                        $sub->select('provider_category')->from('integration_connections as ic')->where('provider_type', 'telegram_bot')->limit(1);
                    });
            })
            ->where('is_active', true)
            ->first();

        if (! $connection) {
            $connection = IntegrationConnection::query()
                ->whereRaw('LOWER(provider_category) = ?', ['telegram'])
                ->where('is_active', true)
                ->first();
        }

        if (! $connection) {
            $this->botToken = config('services.telegram.bot_token');
            $this->chatId = config('services.telegram.admin_chat_id');
        } else {
            $creds = $connection->getCredentials();
            $this->botToken = $creds['bot_token'] ?? $creds['token'] ?? $creds['api_key'] ?? config('services.telegram.bot_token');
            $this->chatId = $creds['admin_chat_id'] ?? $creds['chat_id'] ?? $creds['group_chat_id'] ?? config('services.telegram.admin_chat_id');

            $config = $connection->config_json ?? [];
            if (is_array($config)) {
                $this->chatId = $config['admin_chat_id'] ?? $this->chatId;
            }
        }

        if ($this->botToken && $this->chatId) {
            $this->configured = true;
        }
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    public function sendAlert(string $title, string $body, string $level = 'warning', array $extra = []): bool
    {
        $icon = match (strtolower($level)) {
            'critical', 'danger' => '🚨',
            'error' => '❌',
            'warning', 'warn' => '⚠️',
            'info' => 'ℹ️',
            'success' => '✅',
            default => '📢',
        };

        $lines = [
            "{$icon} *{$title}*",
            "",
            $body,
        ];

        if (! empty($extra)) {
            $lines[] = "";
            $lines[] = "---";
            foreach ($extra as $k => $v) {
                $lines[] = "_{$k}_: " . (is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_SLASHES));
            }
        }

        $lines[] = "";
        $lines[] = "_" . now()->format('d M Y H:i:s') . "_";

        return $this->postMessage(implode("\n", $lines));
    }

    public function sendPayoutRequest(Payout $payout): bool
    {
        $payout->load('preparedBy', 'commissionPlan');

        $lines = [
            "💰 *PERMINTAAN PAYOUT BARU*",
            "",
            "📦 Batch: *{$payout->batch_number}*",
            "💸 Nominal: *Rp " . number_format((float) $payout->total_amount, 0, ',', '.') . "*",
            "📊 Jumlah entry: *{$payout->entry_count}* entri komisi",
            "",
            "👤 Disiapkan oleh: *" . ($payout->preparedBy?->name ?? 'Unknown') . "*",
            "📅 Periode: " . ($payout->period_start_date ? $payout->period_start_date->format('d M Y') : '-') . " s/d " . ($payout->period_end_date ? $payout->period_end_date->format('d M Y') : '-'),
            "🏦 Metode: " . ($payout->payment_method ?? 'Bank Transfer'),
            "",
            "Status: *" . strtoupper($payout->status->value) . "*",
            "",
            "👉 Segera review & approve payout di dashboard admin.",
            "_" . now()->format('d M Y H:i:s') . "_",
        ];

        return $this->postMessage(implode("\n", $lines));
    }

    public function sendNeedsReviewVoucher(VoucherFulfillment $voucher): bool
    {
        $voucher->load('order', 'order.user');

        $lines = [
            "🎟️ *VOUCHER PERLU REVIEW*",
            "",
            "ID Voucher: #{$voucher->id}",
            "Order: *" . ($voucher->order?->number ?? 'N/A') . "*",
            "Customer: *" . ($voucher->order?->user?->name ?? 'N/A') . "*",
            "Total Order: Rp " . number_format((float) ($voucher->order?->total ?? 0), 0, ',', '.'),
            "",
            "Nilai Voucher: " . ($voucher->voucher_value ?? ''),
            "Tipe: " . ($voucher->fulfillment_type ?? 'voucher'),
            "Status: *" . strtoupper($voucher->status?->value ?? 'unknown') . "*",
            "",
            "👉 Voucher menunggu review admin.",
            "_" . now()->format('d M Y H:i:s') . "_",
        ];

        return $this->postMessage(implode("\n", $lines));
    }

    public function sendProviderDegraded(IntegrationConnection $connection, string $errorMessage = ''): bool
    {
        $lines = [
            "📉 *PROVIDER TERDEGRADASI*",
            "",
            "Provider: *{$connection->display_name}*",
            "Kategori: " . (is_object($connection->provider_category) ? $connection->provider_category->value : ($connection->provider_category ?? 'N/A')),
            "Type: *{$connection->provider_type}*",
            "Status: *" . (is_object($connection->status) ? $connection->status->value : ($connection->status ?? 'unknown')) . "*",
            "",
            "Error terakhir: " . ($connection->last_error_message ?: ($errorMessage ?: '-')),
            "Failure count: *" . ((int) $connection->failure_count) . "*",
            "Terakhir sukses: " . ($connection->last_success_at ? $connection->last_success_at->format('d M Y H:i') : 'Belum pernah'),
            "",
            "👉 Segera periksa integrasi di dashboard.",
            "_" . now()->format('d M Y H:i:s') . "_",
        ];

        return $this->postMessage(implode("\n", $lines));
    }

    private function postMessage(string $text): bool
    {
        if (! $this->configured) {
            Log::warning('TelegramAdminNotification tidak dikonfigurasi. Pesan: ' . mb_substr($text, 0, 500));
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $response = Http::timeout(10)->post($url, [
                'chat_id' => $this->chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
            ]);

            $success = $response->successful() && ($response->json('ok') === true);

            if (! $success) {
                Log::warning('Telegram API gagal: ' . $response->body());
            }

            return (bool) $success;
        } catch (\Throwable $e) {
            Log::error('Telegram request exception: ' . $e->getMessage());
            return false;
        }
    }
}
