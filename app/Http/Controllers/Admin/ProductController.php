<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Audit\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('voucherConfig')
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $before = ['status' => $product->status->value ?? $product->status];
        $product->status = $product->isActive() ? 'inactive' : 'active';
        $product->save();

        AuditService::record(
            action: AuditAction::ProductUpdated,
            subject: $product,
            before: $before,
            after: ['status' => $product->status],
        );

        return back()->with('status', 'Status produk diubah.');
    }
}
