<?php

namespace App\Services\Affiliate;

use App\Enums\CommissionEntryStatus;
use App\Enums\CommissionRuleType;
use App\Enums\ProductType;
use App\Models\CommissionEntry;
use App\Models\CommissionPlan;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReferralSnapshot;
use App\Models\Product;
use App\Models\PromoterProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CommissionCalculationService
{
    public function processOrder(Order $order): array
    {
        return DB::transaction(function () use ($order): array {
            $orderId = (int) $order->id;
            $existing = CommissionEntry::query()->where('order_id', $orderId)->count();
            if ($existing > 0) {
                return [];
            }

            $items = OrderItem::query()
                ->where('order_id', $orderId)
                ->get();

            $allEligible = true;
            $snapshots = [];
            foreach ($items as $item) {
                $snap = $this->isProductCommissionEligible($item);
                $snapshots[] = $snap;
                if (! $snap['eligible']) {
                    $allEligible = false;
                }
            }

            if (! $allEligible) {
                return [];
            }

            $ancestry = $this->resolveAncestry($order);
            if (empty($ancestry)) {
                return [];
            }

            $plan = $this->resolveCommissionPlan($order);
            if (! $plan) {
                return [];
            }

            $this->createSnapshot($order, $ancestry, $plan);

            $entries = [];
            foreach ($items as $item) {
                $itemEntries = $this->calculateForItem($order, $item, $ancestry, $plan);
                foreach ($itemEntries as $e) {
                    $entries[] = $e;
                }
            }

            return $entries;
        }, 3);
    }

    private function isProductCommissionEligible(OrderItem $item): array
    {
        $snapshot = $item->product_snapshot_json;
        $productId = $snapshot['product_id'] ?? null;
        $productType = $snapshot['product_type'] ?? null;
        $commissionEligible = $snapshot['commission_eligible'] ?? null;

        if ($commissionEligible === false || $commissionEligible === '0' || $commissionEligible === 0) {
            return ['eligible' => false, 'reason' => 'snapshot_commission_eligible_false'];
        }

        if ($productType === ProductType::Voucher->value) {
            if ($productId) {
                $product = Product::query()->find($productId);
                if ($product && $product->commission_eligible === false) {
                    return ['eligible' => false, 'reason' => 'voucher_product_commission_eligible_false'];
                }
            }

            if ($commissionEligible === null) {
                return ['eligible' => false, 'reason' => 'voucher_default_no_commission'];
            }
        }

        return ['eligible' => true];
    }

    private function resolveAncestry(Order $order): array
    {
        $promoCode = $order->promotor_code_snapshot;
        if (! $promoCode) {
            return [];
        }

        $direct = PromoterProfile::query()
            ->where('stifin_code', $promoCode)
            ->first();

        if (! $direct) {
            return [];
        }

        $ancestry = [
            1 => [
                'level' => 1,
                'promoter_profile_id' => $direct->id,
                'stifin_code' => $direct->stifin_code,
                'name' => $direct->user?->name ?? $direct->stifin_code,
            ],
        ];

        $current = $direct;
        $level = 2;
        $maxLevels = 5;

        while ($level <= $maxLevels && $current && $current->sponsor_promoter_id) {
            $sponsor = PromoterProfile::query()->find($current->sponsor_promoter_id);
            if (! $sponsor) {
                break;
            }

            if ($sponsor->id === $direct->id) {
                break;
            }

            $ancestry[$level] = [
                'level' => $level,
                'promoter_profile_id' => $sponsor->id,
                'stifin_code' => $sponsor->stifin_code,
                'name' => $sponsor->user?->name ?? $sponsor->stifin_code,
            ];

            $current = $sponsor;
            $level++;
        }

        return $ancestry;
    }

    private function resolveCommissionPlan(Order $order): ?CommissionPlan
    {
        $plan = CommissionPlan::query()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if ($plan) {
            return $plan;
        }

        return CommissionPlan::query()
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->first();
    }

    private function createSnapshot(Order $order, array $ancestry, CommissionPlan $plan): OrderReferralSnapshot
    {
        $firstLevel = reset($ancestry);
        $directPid = $firstLevel['promoter_profile_id'] ?? null;

        return OrderReferralSnapshot::query()->create([
            'order_id' => $order->id,
            'direct_promoter_profile_id' => $directPid,
            'ancestry_promoters_json' => $ancestry,
            'commission_plan_id' => $plan->id,
            'snapshot_at' => now(),
        ]);
    }

    private function calculateForItem(Order $order, OrderItem $item, array $ancestry, CommissionPlan $plan): array
    {
        $entries = [];
        $snapshot = $item->product_snapshot_json;
        $productId = $snapshot['product_id'] ?? null;
        $productType = $snapshot['product_type'] ?? null;
        $lineTotal = (float) ($item->total ?? 0);

        $maxLevels = min((int) ($plan->max_levels ?? 5), count($ancestry));

        for ($level = 1; $level <= $maxLevels; $level++) {
            if (! isset($ancestry[$level])) {
                continue;
            }
            $ancestor = $ancestry[$level];

            $rule = $this->resolveRule($plan, $productId, $productType, $level);
            if (! $rule) {
                continue;
            }

            $amount = $this->applyRule($rule, $lineTotal, (float) ($item->quantity ?? 1));

            if ($amount <= 0) {
                continue;
            }

            $referenceId = 'comm-' . $order->id . '-' . $item->id . '-L' . $level . '-' . Str::random(8);

            $exists = CommissionEntry::query()
                ->where('order_id', $order->id)
                ->where('order_item_id', $item->id)
                ->where('beneficiary_promoter_profile_id', $ancestor['promoter_profile_id'])
                ->where('level', $level)
                ->exists();

            if ($exists) {
                continue;
            }

            $entry = CommissionEntry::query()->create([
                'order_item_id' => $item->id,
                'order_id' => $order->id,
                'beneficiary_promoter_profile_id' => $ancestor['promoter_profile_id'],
                'level' => $level,
                'status' => CommissionEntryStatus::Pending,
                'amount' => $amount,
                'amount_paid' => 0,
                'rate_value' => $rule->value,
                'rate_type' => $rule->rule_type,
                'commission_plan_id' => $plan->id,
                'commission_rule_id' => $rule->id,
                'reversed_from_entry_id' => null,
                'payout_id' => null,
                'rejection_reason' => null,
                'earned_at' => now(),
                'locked_at' => null,
                'paid_at' => null,
                'reversed_at' => null,
                'reference_id' => $referenceId,
            ]);

            $entries[] = $entry;
        }

        return $entries;
    }

    private function resolveRule(CommissionPlan $plan, $productId, $productType, int $level): ?CommissionRule
    {
        $query = CommissionRule::query()
            ->where('commission_plan_id', $plan->id)
            ->where('level', $level);

        $clone = (clone $query)->where('product_id', $productId);
        if ($clone->exists()) {
            return $clone->first();
        }

        $clone2 = (clone $query)->where('product_type_filter', $productType);
        if ($clone2->exists()) {
            return $clone2->first();
        }

        $clone3 = (clone $query)->whereNull('product_id')->whereNull('product_type_filter');
        if ($clone3->exists()) {
            return $clone3->first();
        }

        return null;
    }

    private function applyRule(CommissionRule $rule, float $lineTotal, float $qty): float
    {
        $raw = 0;
        if ($rule->rule_type === CommissionRuleType::Percentage) {
            $ratePercent = (float) ($rule->value ?? 0);
            $raw = ($ratePercent / 100.0) * $lineTotal;
        } elseif ($rule->rule_type === CommissionRuleType::FixedAmount) {
            $perUnit = (float) ($rule->value ?? 0);
            $raw = $perUnit * $qty;
        }

        if ($rule->cap_per_unit_max !== null && $rule->cap_per_unit_max > 0) {
            $raw = min($raw, (float) $rule->cap_per_unit_max * $qty);
        }

        return round($raw, 2);
    }
}
