<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analytics_daily_snapshots')) {
            Schema::create('analytics_daily_snapshots', function (Blueprint $table) {
                $table->id();
                $table->date('snapshot_date')->unique();
                $table->decimal('gross_revenue', 15, 2)->default(0);
                $table->decimal('net_revenue', 15, 2)->default(0);
                $table->unsignedInteger('orders_total_count')->default(0);
                $table->unsignedInteger('orders_paid_count')->default(0);
                $table->unsignedInteger('orders_pending_count')->default(0);
                $table->unsignedInteger('orders_needs_review_count')->default(0);
                $table->unsignedInteger('new_contacts_count')->default(0);
                $table->unsignedInteger('promoter_verified_count')->default(0);
                $table->unsignedInteger('voucher_units_sold')->default(0);
                $table->unsignedInteger('coupon_redemption_count')->default(0);
                $table->unsignedBigInteger('point_earned_total')->default(0);
                $table->unsignedBigInteger('point_redeemed_total')->default(0);
                $table->unsignedInteger('campaign_sent_count')->default(0);
                $table->unsignedInteger('campaign_open_count')->default(0);
                $table->unsignedInteger('campaign_click_count')->default(0);
                $table->unsignedInteger('enrollments_count')->default(0);
                $table->unsignedInteger('lessons_completed_count')->default(0);
                $table->unsignedInteger('unique_visitors_count')->default(0);
                $table->unsignedInteger('pageviews_count')->default(0);
                $table->unsignedInteger('leads_count')->default(0);
                $table->unsignedInteger('checkout_initiated_count')->default(0);
                $table->unsignedInteger('purchases_count')->default(0);
                $table->decimal('average_order_value', 15, 2)->default(0);
                $table->decimal('ltv_per_member_avg', 15, 2)->default(0);
                $table->decimal('commission_pending_amount', 15, 2)->default(0);
                $table->decimal('commission_paid_amount', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_snapshots');
    }
};
