<?php

namespace App\Jobs;

use App\Enums\PointDirection;
use App\Enums\PointEntryType;
use App\Models\PointLedgerEntry;
use App\Models\StifinOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class PointExpireSchedulerJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 1800;

    public int $tries = 1;

    public int $timeout = 300;

    public bool $failOnTimeout = true;

    public function uniqueId(): string
    {
        return 'point-expire-scheduler-' . now()->toDateString();
    }

    public function handle(): void
    {
        $now = now();

        $entries = PointLedgerEntry::query()
            ->where('direction', PointDirection::Credit)
            ->where('entry_type', PointEntryType::Earn)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->whereDoesntHave('reversalEntries', function ($q) {
                $q->where('entry_type', PointEntryType::Expire);
            })
            ->lockForUpdate()
            ->get();

        DB::transaction(function () use ($entries, $now) {
            foreach ($entries as $entry) {
                $alreadyExpired = PointLedgerEntry::query()
                    ->where('reversed_from_entry_id', $entry->id)
                    ->where('entry_type', PointEntryType::Expire)
                    ->exists();

                if ($alreadyExpired) {
                    continue;
                }

                $newBalance = ($entry->user->pointsBalance() ?? 0) - (int) $entry->amount_points;

                PointLedgerEntry::query()->create([
                    'user_id' => $entry->user_id,
                    'entry_type' => PointEntryType::Expire,
                    'direction' => PointDirection::Debit,
                    'amount_points' => $entry->amount_points,
                    'balance_after_points' => max(0, $newBalance),
                    'reason_code' => 'point_expired',
                    'reason_text' => 'Poin kadaluarsa otomatis',
                    'related_order_id' => $entry->related_order_id,
                    'related_order_item_id' => $entry->related_order_item_id,
                    'related_promoter_profile_id' => $entry->related_promoter_profile_id,
                    'reference_id' => 'exp-' . $entry->id . '-' . $now->timestamp,
                    'reversed_from_entry_id' => $entry->id,
                    'expires_at' => null,
                    'performed_by_user_id' => null,
                    'meta_json' => [
                        'source_entry_id' => $entry->id,
                        'expired_at' => $entry->expires_at?->toIso8601String(),
                    ],
                ]);
            }
        });
    }

    public function failed(Throwable $exception): void
    {
        DB::transaction(function () use ($exception) {
            StifinOperation::query()->create([
                'operation_type' => 'point.expire_scheduler_failed',
                'request_reference' => 'point-expire-' . now()->toDateString(),
                'request_hash' => hash('sha256', 'point-expire-fail-' . now()->toIso8601String()),
                'redacted_payload_json' => [
                    'run_date' => now()->toDateString(),
                ],
                'response_body_text' => mb_substr($exception->getMessage(), 0, 2000),
                'http_status_code' => null,
                'outcome' => \App\Enums\StifinOperationOutcome::UnexpectedError,
                'branch_code_snapshot' => null,
                'promoter_code_snapshot' => null,
                'actor_user_id' => null,
                'fulfillment_id' => null,
                'initiated_at' => now(),
                'completed_at' => now(),
            ]);
        });
    }
}
