<?php

namespace App\Console\Commands;

use App\Jobs\ProcessVoucherFulfillmentJob;
use App\Models\OutboxEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DispatchOutboxEvents extends Command
{
    protected $signature = 'outbox:dispatch {--limit=50}';

    protected $description = 'Dispatch unprocessed outbox events ke queue worker';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $rows = DB::transaction(function () use ($limit) {
            $query = OutboxEvent::query()
                ->whereNull('processed_at')
                ->whereNull('dispatched_at')
                ->where(function ($q) {
                    $q->whereNull('available_at')->orWhere('available_at', '<=', now());
                })
                ->where('event_type', 'fulfill_voucher')
                ->orderBy('id')
                ->limit($limit);

            $driver = DB::connection()->getDriverName();
            $query = in_array($driver, ['mysql', 'pgsql'], true)
                ? $query->lock('for update skip locked')
                : $query->lockForUpdate();

            $rows = $query->get();

            foreach ($rows as $row) {
                $row->forceFill([
                    'job_uuid' => (string) Str::uuid(),
                    'dispatched_at' => now(),
                ])->save();
            }

            return $rows;
        });

        $count = 0;
        foreach ($rows as $row) {
            ProcessVoucherFulfillmentJob::dispatch($row->id);
            $count++;
        }

        $this->info("Dispatched {$count} outbox event(s).");

        return self::SUCCESS;
    }
}
