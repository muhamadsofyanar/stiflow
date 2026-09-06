<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->where('is_published', true)
            ->where('is_catalog_visible', true)
            ->with('variants');

        if ($request->has('type') && $request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $sort = $request->input('sort', 'default');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('published_at', 'desc');
                break;
            default:
                $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
                break;
        }

        $products = $query->paginate(12);

        return view('public.catalog.index', compact('products'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_published && $product->is_catalog_visible, 404);

        $product->load(['variants', 'orderBumps']);

        return view('public.catalog.show', compact('product'));
    }
}
