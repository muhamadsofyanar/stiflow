<?php

namespace App\Jobs;

use App\Models\OutboxEvent;
use App\Services\Voucher\VoucherFulfillmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessVoucherFulfillmentJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 3600;

    public int $tries = 1;

    public int $timeout = 90;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $outboxEventId,
    ) {
    }

    public function uniqueId(): string
    {
        return 'outbox-voucher-' . $this->outboxEventId;
    }

    public function handle(VoucherFulfillmentService $service): void
    {
        $event = OutboxEvent::query()->find($this->outboxEventId);
        if (! $event) {
            return;
        }
        $service->processOutbox($event);
    }

    public function failed(Throwable $exception): void
    {
        DB::transaction(function () use ($exception) {
            $event = OutboxEvent::query()->find($this->outboxEventId);
            if (! $event) {
                return;
            }
            if ($event->processed_at === null) {
                $event->processed_at = now();
                $event->worker_result = 'job_exception';
                $event->error_message = mb_substr($exception->getMessage(), 0, 500);
                $event->save();
            }
        });
    }
}
