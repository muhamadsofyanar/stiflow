<?php

namespace App\Services\Voucher;

use App\Enums\AuditAction;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use App\Enums\PromoterVerificationStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\PromoterProfile;
use App\Models\User;
use App\Models\VoucherProductConfig;
use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class VoucherOrderService
{
    public function createVoucherOrder(
        User $promotor,
        Product $product,
        int $qty,
    ): Order {
        if (! $promotor->isPromotor()) {
            throw new InvalidArgumentException('Hanya promotor yang dapat membeli voucher.');
        }

        $profile = $promotor->promoterProfile;
        if (! $profile || $profile->verification_status !== PromoterVerificationStatus::Verified) {
            throw new RuntimeException('Akun promotor belum terverifikasi.');
        }

        if (! $product->isVoucher() || ! $product->isActive()) {
            throw new InvalidArgumentException('Produk tidak valid atau tidak aktif.');
        }

        /** @var VoucherProductConfig $config */
        $config = $product->voucherConfig;
        if (! $config) {
            throw new RuntimeException('Konfigurasi produk voucher tidak ditemukan.');
        }

        if ($qty < $config->min_qty || $qty > $config->max_qty) {
            throw new InvalidArgumentException("Kuantitas harus diantara {$config->min_qty} dan {$config->max_qty}.");
        }

        $unitPrice = $config->unit_price;
        $subtotal = $unitPrice * $qty;
        $discount = 0;
        $total = $subtotal - $discount;

        $orderNumber = 'INV-' . date('YmdHis') . '-' . strtoupper(Str::random(6));

        $order = DB::transaction(function () use (
            $promotor,
            $profile,
            $product,
            $config,
            $qty,
            $unitPrice,
            $subtotal,
            $discount,
            $total,
            $orderNumber,
        ) {
            /** @var Order $order */
            $order = Order::query()->create([
                'number' => $orderNumber,
                'user_id' => $promotor->id,
                'promotor_code_snapshot' => $profile->stifin_code,
                'currency' => 'IDR',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => OrderStatus::PendingPayment,
                'expires_at' => now()->addHours(24),
                'notes' => null,
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_snapshot_json' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'type' => $product->type->value ?? ProductType::Voucher->value,
                    'commission_eligible' => $product->commission_eligible,
                    'unit_price' => (float) $unitPrice,
                    'min_qty' => $config->min_qty,
                    'max_qty' => $config->max_qty,
                    'promotor_code_target' => $profile->stifin_code,
                ],
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'discount' => 0,
                'total' => $total,
                'fulfillment_type' => FulfillmentType::Voucher,
            ]);

            PaymentAttempt::query()->create([
                'order_id' => $order->id,
                'method' => 'manual_transfer',
                'provider' => 'internal_manual',
                'amount' => $total,
                'currency' => 'IDR',
                'status' => PaymentStatus::Pending,
            ]);

            return $order;
        });

        AuditService::record(
            action: AuditAction::OrderCreated,
            subject: $order,
            after: [
                'order_id' => $order->id,
                'order_number' => $order->number,
                'promotor_code' => $profile->stifin_code,
                'qty' => $qty,
                'total' => $total,
            ],
        );

        return $order->load(['items', 'paymentAttempts', 'user', 'user.promoterProfile']);
    }
}
