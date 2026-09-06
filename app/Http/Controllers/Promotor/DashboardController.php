<?php

namespace App\Http\Controllers\Promotor;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $orderBase = Order::query()->where('user_id', $user->id);

        $stats = [
            'pending_payment' => (clone $orderBase)->whereIn('status', [OrderStatus::PendingPayment, OrderStatus::PaymentSubmitted])->count(),
            'processing' => (clone $orderBase)->whereIn('status', [OrderStatus::Paid, OrderStatus::Fulfilling])->count(),
            'completed' => (clone $orderBase)->where('status', OrderStatus::Completed)->count(),
            'needs_review' => (clone $orderBase)->where('status', OrderStatus::NeedsReview)->count(),
            'total_spent' => (float) (clone $orderBase)->where('status', OrderStatus::Completed)->sum('total'),
        ];

        $recent = (clone $orderBase)
            ->with(['items', 'paymentAttempts', 'paymentAttempts.proof'])
            ->latest()
            ->limit(10)
            ->get();

        $profile = $user->promoterProfile;
        $branch = \App\Models\BranchSetting::current();

        return view('promotor.dashboard', compact('stats', 'recent', 'profile', 'branch'));
    }
}
