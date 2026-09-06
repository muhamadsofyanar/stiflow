<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SocialProofSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SocialProofAdminController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('socialProofSetting')
            ->latest()
            ->paginate(20);

        return view('admin.social-proof.index', compact('products'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'show_recent_purchase' => 'boolean',
            'time_window_hours' => 'required|integer|min:1|max:720',
            'display_limit' => 'required|integer|min:1|max:100',
            'anonymize_name' => 'boolean',
        ]);

        SocialProofSetting::query()->updateOrCreate(
            ['product_id' => $product->id],
            $validated
        );

        return redirect()->route('admin.social-proof.index')->with('status', 'Setting social proof diperbarui.');
    }
}
