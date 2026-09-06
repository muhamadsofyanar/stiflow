<?php

namespace App\Livewire;

use App\Models\Coupon;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class CheckoutCouponApplier extends Component
{
    public string $code = '';

    public ?array $appliedCoupon = null;

    public string $message = '';

    public string $messageType = 'info';

    public bool $isLoading = false;

    public function mount(): void
    {
        $coupon = Session::get('stiflow_cart.coupon');
        if ($coupon) {
            $this->appliedCoupon = $coupon;
        }
    }

    public function apply(): void
    {
        $this->isLoading = true;
        $this->message = '';

        try {
            $code = strtoupper(trim($this->code));

            if ($code === '') {
                $this->message = 'Masukkan kode kupon.';
                $this->messageType = 'error';
                return;
            }

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
                $this->message = 'Kupon tidak ditemukan atau tidak aktif.';
                $this->messageType = 'error';
                $this->appliedCoupon = null;
                return;
            }

            if ($coupon->max_redemptions_global !== null) {
                $usedCount = $coupon->redemptions()->count();
                if ($usedCount >= $coupon->max_redemptions_global) {
                    $this->message = 'Kupon sudah mencapai batas penggunaan global.';
                    $this->messageType = 'error';
                    $this->appliedCoupon = null;
                    return;
                }
            }

            $cart = Session::get('stiflow_cart', []);
            $cart['coupon'] = [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'max_discount' => $coupon->max_discount !== null ? (float) $coupon->max_discount : null,
                'min_order_total' => (float) ($coupon->min_order_total ?? 0),
            ];
            Session::put('stiflow_cart', $cart);

            $this->appliedCoupon = $cart['coupon'];
            $this->code = '';

            if ($coupon->type === 'percent') {
                $discountText = "Diskon {$coupon->value}%";
                if ($coupon->max_discount) {
                    $discountText .= " (maks Rp " . number_format($coupon->max_discount, 0, ',', '.') . ")";
                }
            } else {
                $discountText = "Potongan Rp " . number_format($coupon->value, 0, ',', '.');
            }

            $this->message = "Kupon {$coupon->code} diterapkan! {$discountText}.";
            $this->messageType = 'success';

            $this->dispatch('coupon-applied', coupon: $this->appliedCoupon);
        } finally {
            $this->isLoading = false;
        }
    }

    public function remove(): void
    {
        $cart = Session::get('stiflow_cart', []);
        if (isset($cart['coupon'])) {
            unset($cart['coupon']);
            Session::put('stiflow_cart', $cart);
        }

        $this->appliedCoupon = null;
        $this->code = '';
        $this->message = 'Kupon dihapus dari pesanan.';
        $this->messageType = 'info';

        $this->dispatch('coupon-removed');
    }

    public function getDiscountLabel(): string
    {
        if (! $this->appliedCoupon) {
            return '';
        }

        $type = $this->appliedCoupon['type'] ?? 'fixed';
        $value = (float) ($this->appliedCoupon['value'] ?? 0);
        $maxDiscount = $this->appliedCoupon['max_discount'] ?? null;

        if ($type === 'percent') {
            $label = "{$value}%";
            if ($maxDiscount !== null) {
                $label .= " (max Rp " . number_format($maxDiscount, 0, ',', '.') . ")";
            }
            return $label;
        }

        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    public function render()
    {
        return view('livewire.checkout-coupon-applier');
    }
}
