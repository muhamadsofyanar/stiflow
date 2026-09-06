<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\View\View;

class CouponRedeemController extends Controller
{
    public function apply(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim((string) $request->input('code')));

        $now = now();
        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->first();

        if (! $coupon) {
            return redirect()->back()->with('error', 'Kupon tidak ditemukan atau tidak aktif.');
        }

        if ($coupon->max_redemptions_global !== null) {
            $usedCount = $coupon->redemptions()->count();
            if ($usedCount >= $coupon->max_redemptions_global) {
                return redirect()->back()->with('error', 'Kupon sudah mencapai batas penggunaan.');
            }
        }

        $cart = Session::get('stiflow_cart', []);
        $cart['coupon'] = [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'max_discount' => $coupon->max_discount !== null ? (float) $coupon->max_discount : null,
        ];
        Session::put('stiflow_cart', $cart);

        return redirect()->back()->with('success', "Kupon {$coupon->code} berhasil diterapkan.");
    }

    public function showResult(Request $request): View
    {
        $success = (bool) $request->session()->get('coupon_success', false);
        $message = (string) $request->session()->get('coupon_message', '');
        $code = (string) $request->session()->get('coupon_code', '');

        return view('public.coupon.redeem-result', compact('success', 'message', 'code'));
    }

    public function remove(Request $request): RedirectResponse
    {
        $cart = Session::get('stiflow_cart', []);
        if (isset($cart['coupon'])) {
            unset($cart['coupon']);
            Session::put('stiflow_cart', $cart);
        }

        return redirect()->back()->with('info', 'Kupon dihapus dari pesanan.');
    }
}
