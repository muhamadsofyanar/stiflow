<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantController extends Controller
{
    public function index(): View
    {
        $variants = ProductVariant::query()->with('product')->latest()->paginate(20);

        return view('admin.variants.index', compact('variants'));
    }

    public function create(Request $request): View
    {
        return view('admin.variants.create', [
            'products' => Product::query()->orderBy('name')->get(),
            'selectedProductId' => $request->integer('product_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ProductVariant::query()->create($this->validated($request));

        return redirect()->route('admin.variants.index')->with('status', 'Variant dibuat.');
    }

    public function show(ProductVariant $variant): View
    {
        $variant->load('product');

        return view('admin.variants.show', compact('variant'));
    }

    public function edit(ProductVariant $variant): View
    {
        return view('admin.variants.edit', [
            'variant' => $variant,
            'products' => Product::query()->orderBy('name')->get(),
            'selectedProductId' => $variant->product_id,
        ]);
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $variant->update($this->validated($request, $variant));

        return redirect()->route('admin.variants.index')->with('status', 'Variant diperbarui.');
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $variant->delete();

        return redirect()->route('admin.variants.index')->with('status', 'Variant dihapus.');
    }

    private function validated(Request $request, ?ProductVariant $variant = null): array
    {
        $productId = $request->integer('product_id');
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_variants', 'sku')
                    ->where(fn ($query) => $query->where('product_id', $productId))
                    ->ignore($variant?->id),
            ],
            'price_override' => 'nullable|numeric|min:0',
            'stock_qty' => 'nullable|integer|min:0',
            'in_stock' => 'boolean',
            'attributes_json' => 'nullable|json',
            'weight_gram' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'license_activation_limit' => 'nullable|string|max:255',
            'license_expiry_days' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['attributes_json'] = filled($validated['attributes_json'] ?? null)
            ? json_decode($validated['attributes_json'], true, 512, JSON_THROW_ON_ERROR)
            : null;
        $validated['in_stock'] = $request->boolean('in_stock');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] ??= 0;

        return $validated;
    }
}
