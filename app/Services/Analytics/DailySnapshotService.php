<?php

namespace App\Services\Analytics;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\AnalyticsDailySnapshot;
use App\Models\Contact;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PointLedgerEntry;
use App\Models\PromoterProfile;
use App\Models\Coupon;
use App\Models\CommissionEntry;
use App\Enums\PointDirection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DailySnapshotService
{
    public function generateForDate(Carbon $date): AnalyticsDailySnapshot
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $ordersBase = Order::query()->whereBetween('created_at', [$start, $end]);
        $ordersPaid = (clone $ordersBase)->where('status', OrderStatus::Paid->value ?? 'paid');
        $ordersPending = (clone $ordersBase)->whereIn('status', ['pending', OrderStatus::Pending->value ?? 'pending']);
        $ordersReview = (clone $ordersBase)->whereIn('status', ['needs_review', 'review']);

        $grossRevenue = (clone $ordersPaid)->sum('grand_total_amount') ?? 0;
        $netRevenue = $grossRevenue - (CommissionEntry::query()
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount') ?? 0);

        $voucherUnits = OrderItem::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('order', fn ($q) => $q->whereIn('status', ['paid', OrderStatus::Paid->value ?? 'paid']))
            ->sum('quantity') ?? 0;

        $pointsEarned = PointLedgerEntry::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('direction', PointDirection::Credit->value ?? 'credit')
            ->sum('points_delta') ?? 0;

        $pointsRedeemed = PointLedgerEntry::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('direction', PointDirection::Debit->value ?? 'debit')
            ->sum('points_delta') ?? 0;

        $commissionPaid = CommissionEntry::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['paid'])
            ->sum('amount') ?? 0;

        $commissionPending = CommissionEntry::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['pending', 'available'])
            ->sum('amount') ?? 0;

        $avgOrderValue = $ordersPaid->count() > 0
            ? ($grossRevenue / $ordersPaid->count())
            : 0;

        $aov = AnalyticsDailySnapshot::query()
            ->whereBetween('snapshot_date', [$date->copy()->subDays(29)->startOfDay(), $date])
            ->avg('average_order_value') ?? $avgOrderValue;

        $snapshot = AnalyticsDailySnapshot::query()->updateOrCreate(
            ['snapshot_date' => $date->toDateString()],
            [
                'gross_revenue' => $grossRevenue,
                'net_revenue' => max(0, $netRevenue),
                'orders_total_count' => $ordersBase->count(),
                'orders_paid_count' => $ordersPaid->count(),
                'orders_pending_count' => $ordersPending->count(),
                'orders_needs_review_count' => $ordersReview->count(),
                'new_contacts_count' => Contact::query()->whereBetween('created_at', [$start, $end])->count(),
                'promoter_verified_count' => PromoterProfile::query()->whereBetween('verified_at', [$start, $end])->count(),
                'voucher_units_sold' => $voucherUnits,
                'coupon_redemption_count' => DB::table('coupons')->whereBetween('redeemed_at', [$start, $end])->count()
                    + DB::table('orders')->whereBetween('created_at', [$start, $end])->whereNotNull('coupon_id')->count(),
                'point_earned_total' => $pointsEarned,
                'point_redeemed_total' => $pointsRedeemed,
                'campaign_sent_count' => 0,
                'campaign_open_count' => 0,
                'campaign_click_count' => 0,
                'enrollments_count' => Enrollment::query()->whereBetween('created_at', [$start, $end])->count(),
                'lessons_completed_count' => LessonProgress::query()->whereBetween('completed_at', [$start, $end])->where('is_completed', true)->count(),
                'unique_visitors_count' => 0,
                'pageviews_count' => 0,
                'leads_count' => Contact::query()->whereBetween('created_at', [$start, $end])->count(),
                'checkout_initiated_count' => DB::table('checkout_initiations')->whereBetween('initiated_at', [$start, $end])->count(),
                'purchases_count' => $ordersPaid->count(),
                'average_order_value' => $aov,
                'ltv_per_member_avg' => 0,
                'commission_pending_amount' => $commissionPending,
                'commission_paid_amount' => $commissionPaid,
            ]
        );

        return $snapshot;
    }
}
