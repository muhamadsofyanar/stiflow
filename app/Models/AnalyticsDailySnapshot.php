<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDailySnapshot extends Model
{
    protected $fillable = [
        'snapshot_date',
        'gross_revenue',
        'net_revenue',
        'orders_total_count',
        'orders_paid_count',
        'orders_pending_count',
        'orders_needs_review_count',
        'new_contacts_count',
        'promoter_verified_count',
        'voucher_units_sold',
        'coupon_redemption_count',
        'point_earned_total',
        'point_redeemed_total',
        'campaign_sent_count',
        'campaign_open_count',
        'campaign_click_count',
        'enrollments_count',
        'lessons_completed_count',
        'unique_visitors_count',
        'pageviews_count',
        'leads_count',
        'checkout_initiated_count',
        'purchases_count',
        'average_order_value',
        'ltv_per_member_avg',
        'commission_pending_amount',
        'commission_paid_amount',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'gross_revenue' => 'decimal:2',
            'net_revenue' => 'decimal:2',
            'average_order_value' => 'decimal:2',
            'ltv_per_member_avg' => 'decimal:2',
            'commission_pending_amount' => 'decimal:2',
            'commission_paid_amount' => 'decimal:2',
        ];
    }
}
