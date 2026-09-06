<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductCatalogController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->with('variants')->latest()->paginate(20);

        return view('admin.products-catalog.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products-catalog.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'type' => 'required|string',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Product::query()->create($validated);

        return redirect()->route('admin.products-catalog.index')->with('status', 'Produk dibuat.');
    }

    public function show(Product $product): View
    {
        $product->load(['variants', 'bumps']);

        return view('admin.products-catalog.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products-catalog.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,'.$product->id,
            'type' => 'required|string',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]));

        return redirect()->route('admin.products-catalog.index')->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products-catalog.index')->with('status', 'Produk dihapus.');
    }
}
