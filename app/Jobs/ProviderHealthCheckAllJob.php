<?php

namespace App\Jobs;

use App\Integrations\Health\AggregateConnectionHealthChecker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProviderHealthCheckAllJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public readonly int $chunkSize = 5,
    ) {
    }

    public function handle(AggregateConnectionHealthChecker $checker): void
    {
        @ini_set('memory_limit', '256M');
        @set_time_limit(0);

        try {
            $summary = $checker->checkAll($this->chunkSize);

            Log::info('ProviderHealthCheckAllJob selesai', $summary);
        } catch (\Throwable $e) {
            Log::error('ProviderHealthCheckAllJob exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        try {
            DB::transaction(function () use ($exception) {
                if (class_exists(\App\Models\StifinOperation::class)) {
                    \App\Models\StifinOperation::query()->create([
                        'operation_type' => 'provider_health_check_failed',
                        'request_reference' => 'health-check-all-' . now()->toIso8601String(),
                        'request_hash' => hash('sha256', 'health-fail-' . now()->toIso8601String()),
                        'redacted_payload_json' => ['chunk_size' => $this->chunkSize],
                        'response_body_text' => mb_substr($exception->getMessage(), 0, 2000),
                        'outcome' => \App\Enums\StifinOperationOutcome::UnexpectedError ?? 'error',
                        'initiated_at' => now(),
                        'completed_at' => now(),
                    ]);
                }
            });
        } catch (\Throwable) {
        }
    }
}
