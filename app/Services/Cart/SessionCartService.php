<?php

namespace App\Services\Cart;

use App\Models\Coupon;
use App\Models\OrderBump;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;

class SessionCartService
{
    public const SESSION_KEY = 'stiflow_cart';

    public function getCart(): array
    {
        return Session::get(self::SESSION_KEY, [
            'items' => [],
            'coupon' => null,
            'bumps' => [],
        ]);
    }

    public function setCart(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
    }

    public function clearCart(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function addProduct(Product $product, ?ProductVariant $variant = null, int $qty = 1): array
    {
        $cart = $this->getCart();

        $unitPrice = (float) ($variant?->price ?? $product->price);

        $cart['items'][] = [
            'type' => 'product',
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'name' => $product->name . ($variant ? " - {$variant->name}" : ''),
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'product_type' => $product->type?->value ?? $product->type,
            'commission_eligible' => $product->commission_eligible,
            'points_eligible' => $product->points_eligible,
        ];

        $this->setCart($cart);

        return $cart;
    }

    public function addBump(OrderBump $bump): array
    {
        $cart = $this->getCart();

        $bumpProduct = $bump->bumpProduct;
        $price = (float) ($bump->discount_price ?? ($bumpProduct?->price ?? 0));

        $cart['bumps'][] = [
            'type' => 'bump',
            'order_bump_id' => $bump->id,
            'primary_product_id' => $bump->primary_product_id,
            'bump_product_id' => $bumpProduct?->id,
            'name' => $bumpProduct?->name ?? 'Order Bump',
            'quantity' => 1,
            'unit_price' => $price,
        ];

        $this->setCart($cart);

        return $cart;
    }

    public function applyCoupon(Coupon $coupon): array
    {
        $cart = $this->getCart();

        $cart['coupon'] = [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'max_discount' => $coupon->max_discount !== null ? (float) $coupon->max_discount : null,
            'min_order_total' => (float) ($coupon->min_order_total ?? 0),
        ];

        $this->setCart($cart);

        return $cart;
    }

    public function removeCoupon(): array
    {
        $cart = $this->getCart();
        $cart['coupon'] = null;
        $this->setCart($cart);

        return $cart;
    }

    public function calculateSubtotal(array $cart = null): float
    {
        $cart = $cart ?? $this->getCart();
        $subtotal = 0;

        foreach (($cart['items'] ?? []) as $item) {
            $subtotal += (float) ($item['unit_price'] ?? 0) * (int) ($item['quantity'] ?? 1);
        }

        foreach (($cart['bumps'] ?? []) as $bump) {
            $subtotal += (float) ($bump['unit_price'] ?? 0) * (int) ($bump['quantity'] ?? 1);
        }

        return round($subtotal, 2);
    }

    public function calculateDiscount(array $cart = null): float
    {
        $cart = $cart ?? $this->getCart();
        $coupon = $cart['coupon'] ?? null;
        if (! $coupon) {
            return 0;
        }

        $subtotal = $this->calculateSubtotal($cart);
        $minOrderTotal = (float) ($coupon['min_order_total'] ?? 0);
        if ($minOrderTotal > 0 && $subtotal < $minOrderTotal) {
            return 0;
        }

        $discount = 0;
        if (($coupon['type'] ?? 'fixed') === 'percent') {
            $discount = ($subtotal * (float) ($coupon['value'] ?? 0)) / 100;
            if (isset($coupon['max_discount']) && $coupon['max_discount'] !== null) {
                $discount = min($discount, (float) $coupon['max_discount']);
            }
        } else {
            $discount = (float) ($coupon['value'] ?? 0);
        }

        return round(min($discount, $subtotal), 2);
    }

    public function calculateTotal(array $cart = null): float
    {
        $cart = $cart ?? $this->getCart();
        $subtotal = $this->calculateSubtotal($cart);
        $discount = $this->calculateDiscount($cart);

        return round(max(0, $subtotal - $discount), 2);
    }

    public function prepareCheckoutData(Product $product, ?ProductVariant $variant = null, ?OrderBump $selectedBump = null): array
    {
        $cart = $this->getCart();

        $items = [
            [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->name . ($variant ? " - {$variant->name}" : ''),
                'unit_price' => (float) ($variant?->price ?? $product->price),
                'quantity' => 1,
                'product_type' => $product->type?->value ?? $product->type,
            ],
        ];

        $bumps = [];
        if ($selectedBump) {
            $bumpProduct = $selectedBump->bumpProduct;
            $bumps[] = [
                'order_bump_id' => $selectedBump->id,
                'name' => $bumpProduct?->name ?? 'Order Bump',
                'unit_price' => (float) ($selectedBump->discount_price ?? ($bumpProduct?->price ?? 0)),
                'quantity' => 1,
            ];
        }

        $subtotal = 0;
        foreach ($items as $i) {
            $subtotal += $i['unit_price'] * $i['quantity'];
        }
        foreach ($bumps as $b) {
            $subtotal += $b['unit_price'] * $b['quantity'];
        }

        $discount = 0;
        $coupon = $cart['coupon'] ?? null;
        if ($coupon) {
            $minOrderTotal = (float) ($coupon['min_order_total'] ?? 0);
            if ($minOrderTotal <= 0 || $subtotal >= $minOrderTotal) {
                if (($coupon['type'] ?? 'fixed') === 'percent') {
                    $discount = ($subtotal * (float) ($coupon['value'] ?? 0)) / 100;
                    if (isset($coupon['max_discount']) && $coupon['max_discount'] !== null) {
                        $discount = min($discount, (float) $coupon['max_discount']);
                    }
                } else {
                    $discount = (float) ($coupon['value'] ?? 0);
                }
                $discount = min($discount, $subtotal);
            }
        }

        return [
            'items' => $items,
            'bumps' => $bumps,
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round(max(0, $subtotal - $discount), 2),
        ];
    }
}
