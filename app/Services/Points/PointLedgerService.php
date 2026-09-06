<?php

namespace App\Services\Points;

use App\Enums\AuditAction;
use App\Enums\PointDirection;
use App\Enums\PointEntryType;
use App\Enums\ProductType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointLedgerEntry;
use App\Models\Product;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PointLedgerService
{
    public function earnFromOrder(Order $order, array $options = []): array
    {
        return DB::transaction(function () use ($order, $options): array {
            $items = OrderItem::query()->where('order_id', $order->id)->get();
            $entries = [];

            foreach ($items as $item) {
                $entry = $this->earnFromOrderItem($order, $item, $options);
                if ($entry) {
                    $entries[] = $entry;
                }
            }

            return $entries;
        });
    }

    public function earnFromOrderItem(Order $order, OrderItem $item, array $options = []): ?PointLedgerEntry
    {
        $snapshot = $item->product_snapshot_json;
        $productId = $snapshot['product_id'] ?? null;
        $productType = $snapshot['product_type'] ?? null;
        $pointsEligible = $snapshot['points_eligible'] ?? null;
        $pointsPolicy = $snapshot['points_policy_json'] ?? [];

        if ($productType === ProductType::Voucher->value) {
            return null;
        }

        if ($pointsEligible === false || $pointsEligible === '0' || $pointsEligible === 0) {
            return null;
        }

        $product = null;
        if ($productId) {
            $product = Product::query()->find($productId);
            if ($product) {
                if ($product->points_eligible === false) {
                    return null;
                }
                if ($product->isVoucher()) {
                    return null;
                }
                if (! empty($product->points_policy_json) && empty($pointsPolicy)) {
                    $pointsPolicy = $product->points_policy_json;
                }
            }
        }

        $earnRate = (float) ($pointsPolicy['rate_per_1000'] ?? 0);
        $baseAmount = (float) ($item->total ?? 0);
        $points = $earnRate > 0 ? (int) round(($baseAmount * $earnRate) / 1000.0) : 0;

        if ($points <= 0) {
            $flat = (int) ($pointsPolicy['flat_points'] ?? 0);
            if ($flat > 0) {
                $points = $flat * (int) ($item->quantity ?? 1);
            }
        }

        if ($points <= 0) {
            return null;
        }

        $userId = $order->user_id;
        $expiresDays = (int) ($pointsPolicy['expires_days'] ?? 365);
        $expiresAt = $expiresDays > 0 ? now()->addDays($expiresDays) : null;

        return $this->earn(
            userId: $userId,
            amountPoints: $points,
            reasonCode: 'order_complete',
            reasonText: 'Poin dari pembelian produk ' . ($snapshot['product_name'] ?? ''),
            options: [
                'related_order_id' => $order->id,
                'related_order_item_id' => $item->id,
                'expires_at' => $expiresAt,
                'actor' => $options['actor'] ?? null,
                'metadata' => [
                    'product_id' => $productId,
                    'product_name' => $snapshot['product_name'] ?? null,
                    'base_amount' => $baseAmount,
                    'rate' => $earnRate,
                ],
            ],
        );
    }

    public function earn(
        int $userId,
        int $amountPoints,
        string $reasonCode,
        string $reasonText = '',
        array $options = [],
    ): PointLedgerEntry {
        if ($amountPoints <= 0) {
            throw new InvalidArgumentException('Amount poin harus positif.');
        }

        return DB::transaction(function () use ($userId, $amountPoints, $reasonCode, $reasonText, $options) {
            $lockBalance = $this->lockAndGetBalance($userId);
            $newBalance = $lockBalance + $amountPoints;

            $referenceId = $options['reference_id'] ?? ('earn-' . $userId . '-' . now()->timestamp . '-' . Str::random(8));

            $entry = PointLedgerEntry::query()->create([
                'user_id' => $userId,
                'entry_type' => PointEntryType::Earn,
                'direction' => PointDirection::Credit,
                'amount_points' => $amountPoints,
                'balance_after_points' => $newBalance,
                'reason_code' => $reasonCode,
                'reason_text' => $reasonText,
                'related_order_id' => $options['related_order_id'] ?? null,
                'related_order_item_id' => $options['related_order_item_id'] ?? null,
                'related_promoter_profile_id' => $options['related_promoter_profile_id'] ?? null,
                'reference_id' => $referenceId,
                'reversed_from_entry_id' => null,
                'expires_at' => $options['expires_at'] ?? null,
                'performed_by_user_id' => $options['actor']->id ?? null,
                'meta_json' => $options['metadata'] ?? null,
            ]);

            AuditService::record(
                action: AuditAction::ProductCreated ?? 'point.earned',
                subject: $entry,
                after: [
                    'user_id' => $userId,
                    'amount' => $amountPoints,
                    'balance_after' => $newBalance,
                    'reason' => $reasonCode,
                ],
                actor: $options['actor'] ?? null,
            );

            return $entry;
        });
    }

    public function redeem(
        int $userId,
        int $amountPoints,
        string $reasonCode,
        string $reasonText = '',
        array $options = [],
    ): PointLedgerEntry {
        if ($amountPoints <= 0) {
            throw new InvalidArgumentException('Amount poin harus positif.');
        }

        return DB::transaction(function () use ($userId, $amountPoints, $reasonCode, $reasonText, $options) {
            $lockBalance = $this->lockAndGetBalance($userId);

            if ($lockBalance < $amountPoints) {
                throw new RuntimeException(
                    "Saldo poin tidak mencukupi (saldo: {$lockBalance}, butuh: {$amountPoints})."
                );
            }

            $newBalance = $lockBalance - $amountPoints;

            $referenceId = $options['reference_id'] ?? ('redeem-' . $userId . '-' . now()->timestamp . '-' . Str::random(8));

            $entry = PointLedgerEntry::query()->create([
                'user_id' => $userId,
                'entry_type' => PointEntryType::Redeem,
                'direction' => PointDirection::Debit,
                'amount_points' => $amountPoints,
                'balance_after_points' => $newBalance,
                'reason_code' => $reasonCode,
                'reason_text' => $reasonText,
                'related_order_id' => $options['related_order_id'] ?? null,
                'related_order_item_id' => $options['related_order_item_id'] ?? null,
                'related_promoter_profile_id' => $options['related_promoter_profile_id'] ?? null,
                'reference_id' => $referenceId,
                'reversed_from_entry_id' => null,
                'expires_at' => null,
                'performed_by_user_id' => $options['actor']->id ?? null,
                'meta_json' => $options['metadata'] ?? null,
            ]);

            AuditService::record(
                action: AuditAction::ProductUpdated ?? 'point.redeemed',
                subject: $entry,
                after: [
                    'user_id' => $userId,
                    'amount' => $amountPoints,
                    'balance_after' => $newBalance,
                    'reason' => $reasonCode,
                ],
                actor: $options['actor'] ?? null,
            );

            return $entry;
        }, 3);
    }

    public function expireEntry(PointLedgerEntry $entry): PointLedgerEntry
    {
        return DB::transaction(function () use ($entry) {
            if ($entry->entry_type !== PointEntryType::Earn) {
                throw new InvalidArgumentException('Hanya entry earn yang dapat diekspire.');
            }
            if ($entry->direction !== PointDirection::Credit) {
                throw new InvalidArgumentException('Hanya entry credit yang dapat diekspire.');
            }

            $already = PointLedgerEntry::query()
                ->where('reversed_from_entry_id', $entry->id)
                ->where('entry_type', PointEntryType::Expire)
                ->exists();
            if ($already) {
                throw new RuntimeException('Entry sudah pernah diekspire.');
            }

            $userId = $entry->user_id;
            $amount = (int) $entry->amount_points;
            $lockBalance = $this->lockAndGetBalance($userId);
            $newBalance = max(0, $lockBalance - $amount);

            return PointLedgerEntry::query()->create([
                'user_id' => $userId,
                'entry_type' => PointEntryType::Expire,
                'direction' => PointDirection::Debit,
                'amount_points' => $amount,
                'balance_after_points' => $newBalance,
                'reason_code' => 'point_expired',
                'reason_text' => 'Poin kadaluarsa',
                'related_order_id' => $entry->related_order_id,
                'related_order_item_id' => $entry->related_order_item_id,
                'related_promoter_profile_id' => $entry->related_promoter_profile_id,
                'reference_id' => 'exp-' . $entry->id . '-' . now()->timestamp,
                'reversed_from_entry_id' => $entry->id,
                'expires_at' => null,
                'performed_by_user_id' => null,
                'meta_json' => [
                    'source_entry_id' => $entry->id,
                    'original_expires_at' => $entry->expires_at?->toIso8601String(),
                ],
            ]);
        });
    }

    private function lockAndGetBalance(int $userId): int
    {
        $rows = DB::table('point_ledger_entries')
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->selectRaw("SUM(CASE WHEN direction = ? THEN amount_points ELSE 0 END) AS credit_sum,
                        SUM(CASE WHEN direction = ? THEN amount_points ELSE 0 END) AS debit_sum", [
                PointDirection::Credit->value,
                PointDirection::Debit->value,
            ])
            ->first();

        $credit = (int) ($rows->credit_sum ?? 0);
        $debit = (int) ($rows->debit_sum ?? 0);

        return $credit - $debit;
    }
}
