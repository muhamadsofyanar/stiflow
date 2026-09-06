<?php

namespace App\Services\Payment;

use App\Enums\AuditAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OutboxEvent;
use App\Models\PaymentProof;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PaymentVerificationService
{
    public function submitProof(
        Order $order,
        UploadedFile $file,
        array $additional = [],
    ): PaymentProof {
        if (! in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::PaymentSubmitted], true)) {
            throw new RuntimeException('Order tidak dalam status menunggu pembayaran.');
        }

        $attempt = $order->latestPaymentAttempt();
        if (! $attempt) {
            throw new RuntimeException('Payment attempt tidak ditemukan.');
        }

        $existing = $attempt->proof;
        if ($existing && $existing->isApproved()) {
            throw new RuntimeException('Pembayaran sudah disetujui, tidak dapat mengunggah bukti baru.');
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new InvalidArgumentException('Hanya file JPG dan PNG yang diperbolehkan.');
        }

        $maxSizeKb = 5 * 1024;
        if ($file->getSize() > $maxSizeKb * 1024) {
            throw new InvalidArgumentException('Ukuran file maksimal 5MB.');
        }

        $filename = 'proof_'.$order->number.'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
        $storedPath = $file->storeAs('payment_proofs', $filename, 'private');

        DB::beginTransaction();
        try {
            $proof = null;
            if ($existing) {
                Storage::disk('private')->delete($existing->file_path);
                $existing->delete();
            }

            /** @var PaymentProof $proof */
            $proof = PaymentProof::query()->create([
                'payment_attempt_id' => $attempt->id,
                'file_path' => $storedPath,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'bank_name' => $additional['bank_name'] ?? null,
                'sender_name' => $additional['sender_name'] ?? null,
                'sender_bank' => $additional['sender_bank'] ?? null,
                'transfer_amount' => $additional['transfer_amount'] ?? $attempt->amount,
                'transfer_time' => $additional['transfer_time'] ?? now(),
                'review_status' => 'pending',
            ]);

            $attempt->status = PaymentStatus::Submitted;
            $attempt->save();

            $order->status = OrderStatus::PaymentSubmitted;
            $order->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if (isset($storedPath)) {
                Storage::disk('private')->delete($storedPath);
            }
            throw $e;
        }

        AuditService::record(
            action: AuditAction::PaymentProofSubmitted,
            subject: $proof,
            after: [
                'order_id' => $order->id,
                'order_number' => $order->number,
                'proof_id' => $proof->id,
                'transfer_amount' => (float) $proof->transfer_amount,
            ],
            actor: $order->user,
        );

        return $proof->load('paymentAttempt', 'paymentAttempt.order');
    }

    public function approvePayment(
        PaymentProof $proof,
        User $admin,
        string $note = '',
    ): void {
        if (! $admin->isStaffOrAbove()) {
            throw new InvalidArgumentException('Hanya admin/staff yang dapat menyetujui pembayaran.');
        }

        DB::transaction(function () use ($proof, $admin, $note) {
            $proof = PaymentProof::query()
                ->where('id', $proof->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($proof->isApproved()) {
                return;
            }
            if ($proof->isRejected()) {
                throw new RuntimeException('Bukti sudah pernah ditolak.');
            }

            $attempt = $proof->paymentAttempt;
            $order = Order::query()
                ->where('id', $attempt->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($order->status, [OrderStatus::PaymentSubmitted, OrderStatus::PendingPayment], true)) {
                throw new RuntimeException("Status order {$order->status->value} tidak dapat disetujui.");
            }

            $attemptAmountCents = (int) round(((float) $attempt->amount) * 100);
            $orderAmountCents = (int) round(((float) $order->total) * 100);
            $proofAmountCents = $proof->transfer_amount === null
                ? null
                : (int) round(((float) $proof->transfer_amount) * 100);

            if ($attemptAmountCents !== $orderAmountCents || $proofAmountCents !== $attemptAmountCents) {
                throw new RuntimeException('Nominal bukti transfer tidak sama dengan tagihan.');
            }

            if (strtoupper(trim($attempt->currency)) !== strtoupper(trim($order->currency))) {
                throw new RuntimeException('Mata uang pembayaran tidak sama dengan order.');
            }

            $oldOrderStatus = $order->status;

            $proof->review_status = 'approved';
            $proof->reviewed_by = $admin->id;
            $proof->reviewed_at = now();
            $proof->review_note = $note;
            $proof->save();

            $attempt->status = PaymentStatus::Verified;
            $attempt->verified_at = now();
            $attempt->save();

            $order->status = OrderStatus::Paid;
            $order->paid_at = now();
            $order->save();

            $orderItem = $order->items()
                ->where('fulfillment_type', 'voucher')
                ->firstOrFail();
            $idempotencyKey = 'voucher-order-item:'.$orderItem->id;
            $correlationId = 'FUL-'.$orderItem->id;

            OutboxEvent::query()->firstOrCreate(
                ['idempotency_key' => $idempotencyKey],
                [
                    'event_type' => 'fulfill_voucher',
                    'aggregate_type' => Order::class,
                    'aggregate_id' => $order->id,
                    'payload_json' => [
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'order_number' => $order->number,
                        'user_id' => $order->user_id,
                        'promotor_code_snapshot' => $order->promotor_code_snapshot,
                        'correlation_id' => $correlationId,
                    ],
                    'correlation_id' => $correlationId,
                    'available_at' => now(),
                ],
            );

            AuditService::record(
                action: AuditAction::PaymentApproved,
                subject: $order,
                before: ['order_status' => $oldOrderStatus?->value ?? $oldOrderStatus],
                after: [
                    'order_status' => OrderStatus::Paid->value,
                    'proof_id' => $proof->id,
                    'admin_id' => $admin->id,
                    'note' => $note,
                ],
                actor: $admin,
            );
        }, 3);
    }

    public function rejectPayment(
        PaymentProof $proof,
        User $admin,
        string $reason,
    ): void {
        if (! $admin->isStaffOrAbove()) {
            throw new InvalidArgumentException('Hanya admin/staff yang dapat menolak pembayaran.');
        }
        if ($reason === '') {
            throw new InvalidArgumentException('Alasan penolakan wajib diisi.');
        }

        DB::transaction(function () use ($proof, $admin, $reason) {
            $proof = PaymentProof::query()
                ->where('id', $proof->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($proof->isApproved() || $proof->isRejected()) {
                return;
            }

            $attempt = $proof->paymentAttempt;
            $order = Order::query()
                ->where('id', $attempt->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            $proof->review_status = 'rejected';
            $proof->reviewed_by = $admin->id;
            $proof->reviewed_at = now();
            $proof->review_note = $reason;
            $proof->save();

            $attempt->status = PaymentStatus::Rejected;
            $attempt->save();

            $order->status = OrderStatus::Rejected;
            $order->save();

            AuditService::record(
                action: AuditAction::PaymentRejected,
                subject: $order,
                after: [
                    'proof_id' => $proof->id,
                    'admin_id' => $admin->id,
                    'reason' => $reason,
                ],
                actor: $admin,
            );
        });
    }
}
