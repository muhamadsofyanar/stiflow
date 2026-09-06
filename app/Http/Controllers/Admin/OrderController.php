<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Services\Audit\AuditService;
use App\Services\Payment\PaymentVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentVerificationService $paymentService,
    ) {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $orders = Order::query()
            ->with(['items', 'paymentAttempts', 'paymentAttempts.proof', 'user', 'user.promoterProfile'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', compact('orders', 'status', 'search'));
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'paymentAttempts', 'paymentAttempts.proof', 'paymentAttempts.proof.reviewer', 'user', 'user.promoterProfile', 'items.fulfillment', 'items.fulfillment.voucherFulfillment', 'items.fulfillment.stifinOperations']);

        return view('admin.orders.show', compact('order'));
    }

    public function approveProof(Request $request, PaymentProof $proof): RedirectResponse
    {
        $note = trim((string) $request->input('note', ''));

        try {
            $this->paymentService->approvePayment($proof, auth()->user(), $note);
            dispatch(new \App\Jobs\ProcessVoucherFulfillmentJob(
                \App\Models\OutboxEvent::query()
                    ->where('aggregate_type', Order::class)
                    ->where('aggregate_id', $proof->paymentAttempt->order_id)
                    ->where('event_type', 'fulfill_voucher')
                    ->latest('id')
                    ->firstOrFail()->id,
            ));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('status', 'Pembayaran disetujui. Pemenuhan voucher berjalan di antrean.');
    }

    public function rejectProof(Request $request, PaymentProof $proof): RedirectResponse
    {
        $reason = trim((string) $request->input('reason', ''));
        try {
            $this->paymentService->rejectPayment($proof, auth()->user(), $reason);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('status', 'Bukti pembayaran ditolak.');
    }
}
