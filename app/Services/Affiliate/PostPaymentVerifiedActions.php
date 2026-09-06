<?php

namespace App\Services\Affiliate;

use App\Enums\OrderStatus;
use App\Jobs\CommissionProcessJob;
use App\Models\Order;
use App\Models\PaymentProof;

class PostPaymentVerifiedActions
{
    public function executeForOrder(Order $order): void
    {
        if ($order->status !== OrderStatus::Paid) {
            return;
        }

        CommissionProcessJob::dispatch($order->id)
            ->delay(now()->addSeconds(10));
    }

    public function executeForProof(PaymentProof $proof): void
    {
        $attempt = $proof->paymentAttempt;
        if (! $attempt) {
            return;
        }

        $order = Order::query()->find($attempt->order_id);
        if (! $order) {
            return;
        }

        if ($order->status === OrderStatus::Paid) {
            $this->executeForOrder($order);
        }
    }
}
