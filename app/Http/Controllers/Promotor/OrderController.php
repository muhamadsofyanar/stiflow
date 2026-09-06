<?php

namespace App\Http\Controllers\Promotor;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PaymentVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentVerificationService $paymentService,
    ) {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['items', 'paymentAttempts', 'paymentAttempts.proof'])
            ->latest()
            ->paginate(10);

        return view('promotor.orders.index', compact('orders', 'status'));
    }

    public function show(Request $request, Order $order): View
    {
        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $order->load(['items', 'paymentAttempts', 'paymentAttempts.proof', 'items.fulfillment', 'items.fulfillment.voucherFulfillment', 'items.fulfillment.stifinOperations']);

        $branch = \App\Models\BranchSetting::current();

        return view('promotor.orders.show', compact('order', 'branch'));
    }

    public function uploadProof(Request $request, Order $order): RedirectResponse
    {
        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'sender_bank' => 'required|string|max:40',
            'sender_name' => 'required|string|max:120',
            'transfer_amount' => 'required|numeric|min:1',
            'transfer_time' => 'required|date',
        ]);

        try {
            $this->paymentService->submitProof(
                $order,
                $request->file('proof'),
                [
                    'sender_bank' => $validated['sender_bank'],
                    'sender_name' => $validated['sender_name'],
                    'transfer_amount' => (float) $validated['transfer_amount'],
                    'transfer_time' => $validated['transfer_time'],
                ],
            );
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('status', 'Bukti pembayaran diunggah. Menunggu verifikasi admin.');
    }
}
