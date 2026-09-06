<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewayConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentGatewayConfigController extends Controller
{
    public function index(): View
    {
        $gateways = PaymentGatewayConfig::query()
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.payment-gateways.index', compact('gateways'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => 'required|in:Xendit,Midtrans,Finpay,ManualBankTransfer',
            'display_name' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
        ]);

        PaymentGatewayConfig::query()->create(array_merge($validated, [
            'is_active' => false,
            'credentials_json' => [],
            'supported_currencies_json' => ['IDR'],
            'minimum_amount' => 0,
            'maximum_amount' => 0,
            'fixed_fee' => 0,
            'percent_fee' => 0,
        ]));

        return redirect()->route('admin.payment-gateways.index')->with('status', 'Gateway ditambahkan.');
    }

    public function toggle(Request $request, PaymentGatewayConfig $gateway): RedirectResponse
    {
        $gateway->update(['is_active' => ! $gateway->is_active]);

        return redirect()->route('admin.payment-gateways.index')->with('status', 'Status gateway diperbarui.');
    }
}
