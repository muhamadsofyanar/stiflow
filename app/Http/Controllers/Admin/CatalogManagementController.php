<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Catalog\ProductCatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogManagementController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('variants')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(20);

        return view('admin.catalog.index', compact('products'));
    }

    public function edit(Product $product): View
    {
        return view('admin.catalog.edit', compact('product'));
    }

    public function update(Request $request, Product $product, ProductCatalogService $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'sort_order' => 'required|integer|min:0',
            'is_catalog_visible' => 'boolean',
            'featured_image' => 'nullable|image|max:5120',
        ]);

        $service->updateCatalogFields($product, $validated);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('catalog-featured', 'public');
            $product->featured_image_path = $path;
            $product->save();
        }

        return redirect()->route('admin.catalog.index')->with('status', 'Produk katalog diperbarui.');
    }

    public function publish(Product $product, ProductCatalogService $service): RedirectResponse
    {
        $service->publish($product);

        return redirect()->route('admin.catalog.index')->with('status', 'Produk dipublikasikan.');
    }

    public function unpublish(Product $product, ProductCatalogService $service): RedirectResponse
    {
        $service->unpublish($product);

        return redirect()->route('admin.catalog.index')->with('status', 'Produk ditarik dari publikasi.');
    }

    public function reorder(Request $request, ProductCatalogService $service): RedirectResponse
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:products,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);

        $service->bulkReorder($validated['orders']);

        return redirect()->route('admin.catalog.index')->with('status', 'Urutan produk diperbarui.');
    }
}
