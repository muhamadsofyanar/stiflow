<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:coupon.manage');
    }

    public function index(Request $request): View
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Coupon::query()->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $coupons = $query->paginate(25);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.create', ['coupon' => new Coupon()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_total' => 'nullable|numeric|min:0',
            'max_redemptions_global' => 'nullable|integer|min:1',
            'max_redemptions_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'active';
        $validated['code'] = strtoupper($validated['code']);

        Coupon::query()->create($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon berhasil dibuat.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.create', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon->id),
            ],
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_total' => 'nullable|numeric|min:0',
            'max_redemptions_global' => 'nullable|integer|min:1',
            'max_redemptions_per_user' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,expired',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Coupon $coupon): RedirectResponse
    {
        $newStatus = $coupon->status === 'active' ? 'inactive' : 'active';
        $coupon->update(['status' => $newStatus]);

        return redirect()->back()->with('success', "Status kupon diubah menjadi {$newStatus}.");
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $usedCount = $coupon->redemptions()->count();
        if ($usedCount > 0) {
            return redirect()->back()->with('error', 'Kupon tidak dapat dihapus karena sudah digunakan.');
        }

        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Kupon berhasil dihapus.');
    }
}
