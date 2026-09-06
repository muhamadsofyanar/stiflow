<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Affiliate\CommissionCalculationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommissionProcessManualController extends Controller
{
    public function __construct(
        private readonly CommissionCalculationService $commissionService,
    ) {
        $this->middleware('can:commissions.process');
    }

    public function index(Request $request): View
    {
        $query = Order::query()
            ->with(['items', 'user'])
            ->where('status', \App\Enums\OrderStatus::Paid)
            ->latest('paid_at')
            ->limit(100);

        $orders = $query->get();

        return view('admin.commissions.manual-process', compact('orders'));
    }

    public function processOrder(Request $request, Order $order): RedirectResponse
    {
        if (! $order->isPaidOrLater()) {
            return redirect()->back()->with('error', 'Order belum dibayar, tidak dapat memproses komisi.');
        }

        try {
            $entries = $this->commissionService->processOrder($order);
            $count = count($entries);

            if ($count === 0) {
                return redirect()->back()->with('info', 'Tidak ada entri komisi baru dibuat (sudah diproses atau tidak eligible).');
            }

            return redirect()->back()->with('success', "Berhasil membuat {$count} entri komisi untuk order #{$order->number}.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memproses komisi: ' . $e->getMessage());
        }
    }
}
