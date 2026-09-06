<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PromoterVerificationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReconciliationCase;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'pending_proof' => Order::query()->where('status', OrderStatus::PaymentSubmitted)->count(),
            'total_orders_today' => Order::query()->whereDate('created_at', today())->count(),
            'completed_orders' => Order::query()->where('status', OrderStatus::Completed)->count(),
            'open_reconciliations' => ReconciliationCase::query()->where('status', \App\Enums\ReconciliationStatus::Open)->count(),
            'active_promotors' => User::query()
                ->where('role', UserRole::Promotor)
                ->whereHas('promoterProfile', fn ($q) => $q->where('verification_status', PromoterVerificationStatus::Verified->value))
                ->count(),
            'pending_promotors' => User::query()
                ->where('role', UserRole::Promotor)
                ->whereHas('promoterProfile', fn ($q) => $q->where('verification_status', PromoterVerificationStatus::Pending->value))
                ->count(),
            'total_promotors' => User::query()->where('role', UserRole::Promotor)->count(),
            'total_revenue_completed' => (float) Order::query()->where('status', OrderStatus::Completed)->sum('total'),
        ];

        $recentOrders = Order::query()
            ->with(['items', 'paymentAttempts', 'paymentAttempts.proof', 'user', 'user.promoterProfile'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
