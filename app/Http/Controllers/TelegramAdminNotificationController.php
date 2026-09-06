<?php

namespace App\Http\Controllers;

use App\Integrations\Telegram\TelegramAdminNotificationAdapter;
use App\Models\Payout;
use App\Models\VoucherFulfillment;
use App\Models\IntegrationConnection;
use Illuminate\Support\Facades\Log;

class TelegramAdminNotificationController extends Controller
{
    public function __construct(
        private readonly TelegramAdminNotificationAdapter $adapter,
    ) {
    }

    public function sendGenericAlert(string $title, string $body, array $extra = [], ?string $level = 'warning'): void
    {
        try {
            $this->adapter->sendAlert($title, $body, $level, $extra);
        } catch (\Throwable $e) {
            Log::warning('TelegramAdminNotification sendAlert gagal: ' . $e->getMessage());
        }
    }

    public function notifyPayoutRequested(Payout $payout): void
    {
        try {
            $this->adapter->sendPayoutRequest($payout);
        } catch (\Throwable $e) {
            Log::warning('Telegram notify payout gagal: ' . $e->getMessage());
        }
    }

    public function notifyVoucherNeedsReview(VoucherFulfillment $voucher): void
    {
        try {
            $this->adapter->sendNeedsReviewVoucher($voucher);
        } catch (\Throwable $e) {
            Log::warning('Telegram notify voucher gagal: ' . $e->getMessage());
        }
    }

    public function notifyProviderDegraded(IntegrationConnection $connection, string $errorMessage = ''): void
    {
        try {
            $this->adapter->sendProviderDegraded($connection, $errorMessage);
        } catch (\Throwable $e) {
            Log::warning('Telegram notify provider degraded gagal: ' . $e->getMessage());
        }
    }
}
