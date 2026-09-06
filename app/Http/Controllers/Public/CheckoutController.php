<?php

namespace App\Http\Controllers\Public;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderBump;
use App\Models\OrderItem;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\SessionCartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly SessionCartService $cartService,
    ) {
    }

    public function show(Request $request, Product $product): View
    {
        $variantId = $request->input('variant_id');
        $bumpId = $request->input('bump_id');

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('id', $variantId)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $orderBumps = OrderBump::query()
            ->where('primary_product_id', $product->id)
            ->where('is_active', true)
            ->with('bumpProduct')
            ->get();

        $selectedBump = null;
        if ($bumpId) {
            $selectedBump = $orderBumps->firstWhere('id', $bumpId);
        }

        $cart = $this->cartService->prepareCheckoutData($product, $variant, $selectedBump);
        $couponApplied = Session::get('stiflow_cart.coupon');

        return view('public.checkout.index', compact(
            'product',
            'variant',
            'orderBumps',
            'selectedBump',
            'cart',
            'couponApplied'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'bump_ids' => 'nullable|array',
            'bump_ids.*' => 'exists:order_bumps,id',
            'billing_name' => 'required|string|max:150',
            'billing_email' => 'required|email|max:150',
            'billing_phone' => 'nullable|string|max:30',
            'promotor_code' => 'nullable|string|max:50',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::query()->findOrFail((int) $request->input('product_id'));
            $variant = null;
            if ($request->filled('variant_id')) {
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('id', (int) $request->input('variant_id'))
                    ->firstOrFail();
            }

            $bumpIds = $request->input('bump_ids', []);
            $bumps = OrderBump::query()
                ->whereIn('id', $bumpIds)
                ->where('primary_product_id', $product->id)
                ->where('is_active', true)
                ->with('bumpProduct')
                ->get();

            $cart = Session::get('stiflow_cart', []);
            $couponData = $cart['coupon'] ?? null;
            $coupon = null;

            if ($couponData && isset($couponData['id'])) {
                $coupon = Coupon::query()->find((int) $couponData['id']);
            }

            $subtotal = (float) ($variant?->price ?? $product->price);
            $discountAmount = 0;

            foreach ($bumps as $bump) {
                $bumpProduct = $bump->bumpProduct;
                if ($bumpProduct) {
                    $subtotal += (float) ($bump->discount_price ?? $bumpProduct->price);
                }
            }

            if ($coupon) {
                if ($coupon->min_order_total > 0 && $subtotal < (float) $coupon->min_order_total) {
                    return redirect()->back()->with('error', "Total pesanan kurang dari minimum Rp {$coupon->min_order_total} untuk kupon ini.")->withInput();
                }

                if ($coupon->type === 'percent') {
                    $discountAmount = ($subtotal * (float) $coupon->value) / 100;
                    if ($coupon->max_discount !== null) {
                        $discountAmount = min($discountAmount, (float) $coupon->max_discount);
                    }
                } else {
                    $discountAmount = (float) $coupon->value;
                }
                $discountAmount = min($discountAmount, $subtotal);
            }

            $total = max(0, $subtotal - $discountAmount);

            $userId = auth()->check() ? auth()->id() : null;

            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(8));

            $billingInfo = [
                'name' => $request->input('billing_name'),
                'email' => $request->input('billing_email'),
                'phone' => $request->input('billing_phone'),
            ];

            $order = Order::query()->create([
                'number' => $orderNumber,
                'user_id' => $userId,
                'promotor_code_snapshot' => $request->input('promotor_code')
                    ?: Session::get('stiflow_referral_code'),
                'currency' => 'IDR',
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'total' => $total,
                'status' => OrderStatus::PendingPayment,
                'coupon_id' => $coupon?->id,
                'notes' => $request->input('notes'),
                'expires_at' => now()->addDays(1),
                'referral_snapshot_json' => $billingInfo,
            ]);

            $this->createOrderItem($order, $product, $variant, 1);

            foreach ($bumps as $bump) {
                $bumpProduct = $bump->bumpProduct;
                if ($bumpProduct) {
                    $bumpPrice = (float) ($bump->discount_price ?? $bumpProduct->price);
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'quantity' => 1,
                        'unit_price' => $bumpPrice,
                        'discount' => 0,
                        'total' => $bumpPrice,
                        'product_snapshot_json' => [
                            'product_id' => $bumpProduct->id,
                            'name' => $bumpProduct->name,
                            'product_type' => $bumpProduct->type?->value ?? $bumpProduct->type,
                            'commission_eligible' => $bumpProduct->commission_eligible,
                            'points_eligible' => $bumpProduct->points_eligible,
                            'order_bump_id' => $bump->id,
                            'is_order_bump' => true,
                        ],
                    ]);
                }
            }

            PaymentAttempt::query()->create([
                'order_id' => $order->id,
                'gateway' => $request->input('payment_method', 'manual_transfer'),
                'amount' => $total,
                'currency' => 'IDR',
                'status' => 'pending',
                'attempt_reference' => 'MANUAL-' . $order->number,
                'expires_at' => now()->addDays(1),
            ]);

            Session::forget('stiflow_cart.coupon');

            return redirect()->route('promotor.orders.show', $order)
                ->with('success', 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran.');
        });
    }

    private function createOrderItem(Order $order, Product $product, ?ProductVariant $variant, int $qty = 1): OrderItem
    {
        $unitPrice = (float) ($variant?->price ?? $product->price);
        $total = $unitPrice * $qty;

        return OrderItem::query()->create([
            'order_id' => $order->id,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'discount' => 0,
            'total' => $total,
            'product_snapshot_json' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'product_type' => $product->type?->value ?? $product->type,
                'variant_id' => $variant?->id,
                'variant_name' => $variant?->name,
                'commission_eligible' => $product->commission_eligible,
                'points_eligible' => $product->points_eligible,
                'points_policy_json' => $product->points_policy_json,
                'variant_price' => $variant?->price,
                'is_main_product' => true,
            ],
        ]);
    }
}
