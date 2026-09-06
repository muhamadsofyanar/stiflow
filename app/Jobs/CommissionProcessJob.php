<?php

namespace App\Jobs;

use App\Enums\AuditAction;
use App\Models\Order;
use App\Models\StifinOperation;
use App\Services\Affiliate\CommissionCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommissionProcessJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 3600;

    public int $tries = 3;

    public int $timeout = 120;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $orderId,
    ) {
    }

    public function uniqueId(): string
    {
        return 'commission-process-order-' . $this->orderId;
    }

    public function handle(CommissionCalculationService $service): void
    {
        $order = Order::query()->find($this->orderId);
        if (! $order) {
            return;
        }

        $service->processOrder($order);
    }

    public function failed(Throwable $exception): void
    {
        DB::transaction(function () use ($exception) {
            $order = Order::query()->find($this->orderId);

            StifinOperation::query()->create([
                'operation_type' => 'commission.process_failed',
                'request_reference' => 'order-' . $this->orderId,
                'request_hash' => hash('sha256', 'commission-fail-' . $this->orderId . '-' . now()->toIso8601String()),
                'redacted_payload_json' => [
                    'order_id' => $this->orderId,
                    'order_number' => $order?->number,
                ],
                'response_body_text' => mb_substr($exception->getMessage(), 0, 2000),
                'http_status_code' => null,
                'outcome' => \App\Enums\StifinOperationOutcome::UnexpectedError,
                'branch_code_snapshot' => null,
                'promoter_code_snapshot' => $order?->promotor_code_snapshot,
                'actor_user_id' => $order?->user_id,
                'fulfillment_id' => null,
                'initiated_at' => now(),
                'completed_at' => now(),
            ]);
        });
    }
}
