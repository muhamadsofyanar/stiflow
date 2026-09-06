<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsDailySnapshot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $days = (int) $request->input('days', 30);
        $startDate = Carbon::today()->subDays($days - 1);
        $endDate = Carbon::today();

        $snapshots = AnalyticsDailySnapshot::query()
            ->whereBetween('snapshot_date', [$startDate, $endDate])
            ->get();

        $cards = [
            'gross_revenue' => $snapshots->sum('gross_revenue'),
            'net_revenue' => $snapshots->sum('net_revenue'),
            'orders_total_count' => $snapshots->sum('orders_total_count'),
            'orders_paid_count' => $snapshots->sum('orders_paid_count'),
            'orders_pending_count' => $snapshots->sum('orders_pending_count'),
            'orders_needs_review_count' => $snapshots->sum('orders_needs_review_count'),
            'new_contacts_count' => $snapshots->sum('new_contacts_count'),
            'promoter_verified_count' => $snapshots->sum('promoter_verified_count'),
            'voucher_units_sold' => $snapshots->sum('voucher_units_sold'),
            'average_order_value' => $snapshots->avg('average_order_value') ?? 0,
            'enrollments_count' => $snapshots->sum('enrollments_count'),
            'lessons_completed_count' => $snapshots->sum('lessons_completed_count'),
            'unique_visitors_count' => $snapshots->sum('unique_visitors_count'),
            'purchases_count' => $snapshots->sum('purchases_count'),
            'commission_paid_amount' => $snapshots->sum('commission_paid_amount'),
        ];

        $periodLabel = "{$startDate->format('d M Y')} — {$endDate->format('d M Y')}";

        return view('admin.analytics.dashboard', compact('cards', 'periodLabel', 'days', 'snapshots'));
    }
}
