<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index()
    {
        $variants = ProductVariant::query()->with('product')->latest()->paginate(20);

        return view('admin.variants.index', compact('variants'));
    }

    public function create()
    {
        return view('admin.variants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ProductVariant::query()->create($request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:product_variants,sku',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]));

        return redirect()->route('admin.variants.index')->with('status', 'Variant dibuat.');
    }

    public function show(ProductVariant $variant)
    {
        return view('admin.variants.show', compact('variant'));
    }

    public function edit(ProductVariant $variant)
    {
        return view('admin.variants.edit', compact('variant'));
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $variant->update($request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:product_variants,sku,'.$variant->id,
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]));

        return redirect()->route('admin.variants.index')->with('status', 'Variant diperbarui.');
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $variant->delete();

        return redirect()->route('admin.variants.index')->with('status', 'Variant dihapus.');
    }
}
