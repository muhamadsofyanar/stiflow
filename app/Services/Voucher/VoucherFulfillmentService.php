<?php

namespace App\Services\Voucher;

use App\Enums\AuditAction;
use App\Enums\FulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\ReconciliationStatus;
use App\Enums\StifinOperationOutcome;
use App\Integrations\Stifin\StifinApiClient;
use App\Models\BranchSetting;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OutboxEvent;
use App\Models\PromoterProfile;
use App\Models\ReconciliationCase;
use App\Models\StifinOperation;
use App\Models\VoucherFulfillment;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Throwable;

class VoucherFulfillmentService
{
    public function __construct(
        private readonly StifinApiClient $stifin,
    ) {}

    /**
     * Main entry: dipanggil oleh queue job.
     * Idempoten: jika fulfillment sudah sukses, keluar tanpa POST.
     */
    public function processOutbox(OutboxEvent $event): void
    {
        $event = DB::transaction(function () use ($event): ?OutboxEvent {
            $lockedEvent = OutboxEvent::query()
                ->whereKey($event->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedEvent || $lockedEvent->processed_at !== null || $lockedEvent->worker_result === 'processing') {
                return null;
            }

            $lockedEvent->worker_result = 'processing';
            $lockedEvent->save();

            return $lockedEvent;
        });

        if (! $event) {
            return;
        }

        $payload = $event->payload_json ?? [];
        $orderId = $payload['order_id'] ?? null;
        $orderItemId = $payload['order_item_id'] ?? null;
        $correlationId = $payload['correlation_id'] ?? $event->correlation_id ?? ('MANUAL-'.$event->id);

        if (! $orderId || ! $orderItemId) {
            $this->markOutboxFailed($event, 'Payload tidak lengkap');

            return;
        }

        DB::beginTransaction();
        try {
            $order = Order::query()
                ->where('id', $orderId)
                ->lockForUpdate()
                ->first();

            $orderItem = OrderItem::query()
                ->where('id', $orderItemId)
                ->where('order_id', $orderId)
                ->first();

            if (! $order || ! $orderItem) {
                DB::commit();
                $this->markOutboxFailed($event, 'Order/Order item tidak ditemukan');

                return;
            }

            if (! in_array($order->status, [OrderStatus::Paid, OrderStatus::Fulfilling, OrderStatus::NeedsReview], true)) {
                DB::commit();
                $this->markOutboxProcessed($event, 'skipped_invalid_status');

                return;
            }

            $fulfillment = Fulfillment::query()
                ->firstOrCreate(
                    ['order_item_id' => $orderItem->id, 'type' => $orderItem->fulfillment_type?->value ?? 'voucher'],
                    [
                        'status' => FulfillmentStatus::Pending,
                        'correlation_id' => $correlationId,
                        'attempts' => 0,
                        'payload_snapshot_json' => $orderItem->product_snapshot_json,
                    ],
                );

            if ($fulfillment->status === FulfillmentStatus::Success) {
                DB::commit();
                $this->markOutboxProcessed($event, 'already_success_idempotent');

                return;
            }

            $fulfillment->status = FulfillmentStatus::Processing;
            $fulfillment->started_at = now();
            $fulfillment->attempts = (int) $fulfillment->attempts + 1;
            $fulfillment->save();

            $order->status = OrderStatus::Fulfilling;
            $order->save();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->markOutboxFailed($event, 'Precondition error: '.$e->getMessage());

            return;
        }

        try {
            $this->runFulfillmentLogic($order, $orderItem, $fulfillment, $event);
            $this->markOutboxProcessed($event, $fulfillment->status->value);
        } catch (Throwable $e) {
            DB::transaction(function () use ($fulfillment, $order, $e) {
                $fulfillment->refresh();
                $fulfillment->error_message = mb_substr($e->getMessage(), 0, 1000);
                if ($fulfillment->status !== FulfillmentStatus::Success && $fulfillment->status !== FulfillmentStatus::NeedsReview) {
                    $fulfillment->status = FulfillmentStatus::Failed;
                    $fulfillment->completed_at = now();
                }
                $fulfillment->save();

                $order->refresh();
                if ($order->status !== OrderStatus::Completed) {
                    $order->status = OrderStatus::NeedsReview;
                    $order->save();
                }

                ReconciliationCase::query()->firstOrCreate(
                    [
                        'subject_type' => Fulfillment::class,
                        'subject_id' => $fulfillment->id,
                        'status' => ReconciliationStatus::Open,
                    ],
                    [
                        'reason' => 'Exception sebelum hasil fulfillment dapat dipastikan.',
                        'evidence_json' => [
                            'error' => mb_substr($e->getMessage(), 0, 500),
                            'automatic_retry_allowed' => false,
                        ],
                    ],
                );
            });
            $this->markOutboxFailed($event, 'Exception: '.$e->getMessage());
        }
    }

    private function runFulfillmentLogic(
        Order $order,
        OrderItem $orderItem,
        Fulfillment $fulfillment,
        OutboxEvent $event,
    ): void {
        $snapshot = $orderItem->product_snapshot_json ?? [];
        $targetCode = $snapshot['promotor_code_target'] ?? null;
        $qty = (int) $orderItem->quantity;
        $branch = BranchSetting::query()->firstOrFail();
        $branchCode = $branch->branch_code;

        $profile = PromoterProfile::query()
            ->where('user_id', $order->user_id)
            ->firstOrFail();

        if (! $profile->isVerified()) {
            throw new \RuntimeException('Promotor tidak terverifikasi saat fulfillment.');
        }

        if (! $targetCode || $targetCode !== $profile->stifin_code || $targetCode !== $order->promotor_code_snapshot) {
            throw new \RuntimeException('Ketidakkonsistenan kode promotor.');
        }

        $reference = 'DISPOS-ORDER-'.$order->number.'-'.$orderItem->id;

        $existingSuccessOp = StifinOperation::query()
            ->where('request_reference', $reference)
            ->where('operation_type', 'add_voucher')
            ->where('outcome', StifinOperationOutcome::Success)
            ->first();

        if ($existingSuccessOp) {
            DB::transaction(function () use ($order, $fulfillment, $existingSuccessOp, $profile, $qty, $targetCode, $reference) {
                $vf = VoucherFulfillment::query()->updateOrCreate(
                    ['fulfillment_id' => $fulfillment->id],
                    [
                        'promoter_profile_id' => $profile->id,
                        'stifin_code_snapshot' => $targetCode,
                        'paid_units' => $qty,
                        'free_units' => 0,
                        'stifin_reference' => $reference,
                    ],
                );
                $fulfillment->status = FulfillmentStatus::Success;
                $fulfillment->completed_at = now();
                $fulfillment->response_snapshot_json = [
                    'skipped_reason' => 'idempotent_existing_success',
                    'original_operation_id' => $existingSuccessOp->id,
                ];
                $fulfillment->save();
                $order->status = OrderStatus::Completed;
                $order->completed_at = now();
                $order->save();
            });
            AuditService::record(
                action: AuditAction::VoucherFulfillmentSuccess,
                subject: $fulfillment,
                after: ['idempotent_skip' => true, 'reference' => $reference],
                metadata: ['stifin_operation_id' => $existingSuccessOp->id],
            );

            return;
        }

        $preBalance = $this->stifin->getVoucherBalance($targetCode);

        $payload = [
            'KodeID' => $targetCode,
            'Jumlah' => (string) $qty,
            'JmlFree' => '0',
            'SaldoJ' => (string) $preBalance['paid'],
            'SaldoF' => (string) $preBalance['free'],
            'Dispos' => $reference,
            'Ket' => 'Pembelian voucher via STIFLOW cabang '.$branchCode.' - Order '.$order->number,
            'UserID' => $this->stifin->getUserIdIdentifier(),
        ];

        $redactedPayload = $payload;
        $redactedPayload['UserID'] = '***';
        $requestHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES));

        $initiatedAt = now();

        try {
            $result = $this->stifin->addVoucher($branchCode, $payload);
        } catch (Throwable $e) {
            $outcome = StifinOperationOutcome::PreflightError;
            $op = StifinOperation::query()->create([
                'operation_type' => 'add_voucher',
                'request_reference' => $reference,
                'request_hash' => $requestHash,
                'redacted_payload_json' => $redactedPayload,
                'response_body_text' => $e->getMessage(),
                'http_status_code' => null,
                'outcome' => $outcome,
                'branch_code_snapshot' => $branchCode,
                'promoter_code_snapshot' => $targetCode,
                'fulfillment_id' => $fulfillment->id,
                'initiated_at' => $initiatedAt,
                'completed_at' => now(),
            ]);
            $this->markNeedsReview($order, $fulfillment, $op, $profile, $qty, $reference, 'Exception saat call API');

            return;
        }

        $outcome = $result->outcome();
        $op = StifinOperation::query()->create([
            'operation_type' => 'add_voucher',
            'request_reference' => $reference,
            'request_hash' => $requestHash,
            'redacted_payload_json' => $redactedPayload,
            'response_body_text' => $result->rawBody,
            'http_status_code' => $result->httpCode,
            'outcome' => $outcome,
            'branch_code_snapshot' => $branchCode,
            'promoter_code_snapshot' => $targetCode,
            'fulfillment_id' => $fulfillment->id,
            'initiated_at' => $initiatedAt,
            'completed_at' => now(),
        ]);

        $postBalance = null;
        try {
            $postBalance = $this->stifin->getVoucherBalance($targetCode);
        } catch (Throwable) {
        }

        if ($outcome === StifinOperationOutcome::Success) {
            DB::transaction(function () use ($order, $fulfillment, $profile, $qty, $targetCode, $reference, $preBalance, $postBalance, $result) {
                $vf = VoucherFulfillment::query()->updateOrCreate(
                    ['fulfillment_id' => $fulfillment->id],
                    [
                        'promoter_profile_id' => $profile->id,
                        'stifin_code_snapshot' => $targetCode,
                        'paid_units' => $qty,
                        'free_units' => 0,
                        'balance_before_paid' => $preBalance['paid'],
                        'balance_before_free' => $preBalance['free'],
                        'balance_after_paid' => $postBalance['paid'] ?? null,
                        'balance_after_free' => $postBalance['free'] ?? null,
                        'stifin_reference' => $reference,
                    ],
                );
                $fulfillment->status = FulfillmentStatus::Success;
                $fulfillment->completed_at = now();
                $fulfillment->response_snapshot_json = [
                    'parsed' => $result->parsedData,
                    'balance_after' => $postBalance,
                ];
                $fulfillment->save();
                $order->status = OrderStatus::Completed;
                $order->completed_at = now();
                $order->save();
            });
            AuditService::record(
                action: AuditAction::VoucherFulfillmentSuccess,
                subject: $fulfillment,
                after: [
                    'qty' => $qty,
                    'target' => $targetCode,
                    'reference' => $reference,
                    'stifin_operation_id' => $op->id,
                ],
            );

            return;
        }

        if ($result->isAmbiguous() || $outcome === StifinOperationOutcome::Fail5xx) {
            $this->markNeedsReview(
                $order,
                $fulfillment,
                $op,
                $profile,
                $qty,
                $reference,
                $result->errorMessage ?? ('Hasil ambigues/outcome='.$outcome->value),
                $preBalance,
                $postBalance,
                $targetCode,
            );

            return;
        }

        DB::transaction(function () use ($order, $fulfillment, $result) {
            $fulfillment->status = FulfillmentStatus::Failed;
            $fulfillment->error_message = mb_substr($result->errorMessage ?? 'HTTP '.($result->httpCode ?? '?').': '.$result->rawBody, 0, 1000);
            $fulfillment->completed_at = now();
            $fulfillment->response_snapshot_json = is_array($result->parsedData) ? $result->parsedData : null;
            $fulfillment->save();
            $order->status = OrderStatus::NeedsReview;
            $order->save();
        });

        AuditService::record(
            action: AuditAction::VoucherFulfillmentFailed,
            subject: $fulfillment,
            after: [
                'stifin_operation_id' => $op->id,
                'outcome' => $outcome->value,
                'reference' => $reference,
            ],
        );
    }

    private function markNeedsReview(
        Order $order,
        Fulfillment $fulfillment,
        StifinOperation $op,
        PromoterProfile $profile,
        int $qty,
        string $reference,
        string $note,
        ?array $preBalance = null,
        ?array $postBalance = null,
        ?string $targetCode = null,
    ): void {
        DB::transaction(function () use (
            $order,
            $fulfillment,
            $op,
            $profile,
            $qty,
            $reference,
            $note,
            $preBalance,
            $postBalance,
            $targetCode,
        ) {
            $targetCode ??= $profile->stifin_code;
            $vf = VoucherFulfillment::query()->firstOrCreate(
                ['fulfillment_id' => $fulfillment->id],
                [
                    'promoter_profile_id' => $profile->id,
                    'stifin_code_snapshot' => $targetCode,
                    'paid_units' => $qty,
                    'free_units' => 0,
                    'balance_before_paid' => $preBalance['paid'] ?? null,
                    'balance_before_free' => $preBalance['free'] ?? null,
                    'balance_after_paid' => $postBalance['paid'] ?? null,
                    'balance_after_free' => $postBalance['free'] ?? null,
                    'stifin_reference' => $reference,
                    'notes' => $note,
                ],
            );
            $fulfillment->status = FulfillmentStatus::NeedsReview;
            $fulfillment->completed_at = now();
            $fulfillment->error_message = $note;
            $fulfillment->save();
            $order->status = OrderStatus::NeedsReview;
            $order->save();

            ReconciliationCase::query()->create([
                'subject_type' => Fulfillment::class,
                'subject_id' => $fulfillment->id,
                'reason' => $note,
                'evidence_json' => [
                    'stifin_operation_id' => $op->id,
                    'reference' => $reference,
                    'outcome' => $op->outcome?->value,
                    'http_status' => $op->http_status_code,
                    'pre_balance' => $preBalance,
                    'post_balance' => $postBalance,
                ],
                'status' => ReconciliationStatus::Open,
            ]);
        });

        AuditService::record(
            action: AuditAction::VoucherFulfillmentNeedsReview,
            subject: $fulfillment,
            after: [
                'stifin_operation_id' => $op->id,
                'outcome' => $op->outcome?->value,
                'note' => $note,
                'reference' => $reference,
            ],
        );
    }

    private function markOutboxProcessed(OutboxEvent $event, string $result): void
    {
        $event->processed_at = now();
        $event->worker_result = $result;
        $event->error_message = null;
        $event->save();
    }

    private function markOutboxFailed(OutboxEvent $event, string $error): void
    {
        $event->processed_at = now();
        $event->worker_result = 'failed';
        $event->error_message = mb_substr($error, 0, 500);
        $event->save();
    }
}
