<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $preset = (int) $request->query('preset', 1);
        $product = Product::query()
            ->voucher()
            ->active()
            ->with('voucherConfig')
            ->latest()
            ->firstOrFail();

        $branch = \App\Models\BranchSetting::current();
        $profile = auth()->user()->promoterProfile;

        return view('promotor.checkout.index', compact('product', 'preset', 'branch', 'profile'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:1|max:500',
        ]);

        $product = Product::query()
            ->voucher()
            ->active()
            ->with('voucherConfig')
            ->latest()
            ->firstOrFail();

        try {
            $order = app(\App\Services\Voucher\VoucherOrderService::class)
                ->createVoucherOrder(auth()->user(), $product, (int) $validated['qty']);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('promotor.orders.show', $order)->with('status', 'Order dibuat. Silakan upload bukti transfer.');
    }
}
