<?php

namespace App\Services\SocialProof;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\SocialProofSetting;

class RecentPurchaseService
{
    public function getRecentPurchasesForProduct(int $productId, ?int $windowHours = null): array
    {
        $setting = SocialProofSetting::query()
            ->where('product_id', $productId)
            ->first();

        if ($setting && ! $setting->show_recent_purchase) {
            return [];
        }

        $hours = $windowHours ?? ($setting?->time_window_hours ?? 48);
        $limit = $setting?->display_limit ?? 10;
        $anonymize = $setting?->anonymize_name ?? true;

        $since = now()->subHours($hours);

        $rows = OrderItem::query()
            ->with(['order.contact', 'order.user'])
            ->where('product_id', $productId)
            ->whereHas('order', function ($q) use ($since) {
                $q->whereBetween('created_at', [$since, now()])
                    ->whereIn('status', [
                        OrderStatus::Paid->value ?? 'paid',
                        'completed', 'paid',
                    ]);
            })
            ->latest('id')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($rows as $item) {
            $order = $item->order;
            $name = $order->contact?->name
                ?? $order->user?->name
                ?? ($order->billing_name ?? 'Pelanggan');

            if ($anonymize) {
                $name = $this->anonymizeName($name);
            }

            $result[] = [
                'name' => $name,
                'city' => $order->contact?->city ?? $order->billing_city ?? '',
                'variant' => $item->variant_name ?? '',
                'quantity' => (int) $item->quantity,
                'amount' => (float) ($item->unit_price_amount ?? 0),
                'purchased_at' => $order->created_at,
                'time_ago_human' => $order->created_at->diffForHumans(),
            ];
        }

        if (count($result) === 0) {
            return [];
        }

        return $result;
    }

    private function anonymizeName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'Pelanggan';
        }
        $parts = preg_split('/\s+/', $name);
        if (count($parts) === 1) {
            $w = $parts[0];

            return mb_strlen($w) <= 2 ? $w.'**' : mb_substr($w, 0, 1).str_repeat('*', max(2, mb_strlen($w) - 2)).mb_substr($w, -1);
        }

        $first = $parts[0];
        $last = $parts[count($parts) - 1];
        $maskedFirst = mb_strlen($first) <= 2 ? $first.'**' : mb_substr($first, 0, 1).str_repeat('*', max(2, mb_strlen($first) - 2)).mb_substr($first, -1);
        $maskedLast = mb_strlen($last) <= 1 ? $last : mb_substr($last, 0, 1).'.';

        return $maskedFirst.' '.$maskedLast;
    }
}
