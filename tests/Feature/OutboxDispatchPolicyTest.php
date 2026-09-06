<?php

namespace Tests\Feature;

use App\Jobs\ProcessVoucherFulfillmentJob;
use App\Models\OutboxEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class OutboxDispatchPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_only_never_dispatched_safe_voucher_event(): void
    {
        Bus::fake();

        $pending = $this->event('pending');
        $this->event('already-dispatched', ['dispatched_at' => now(), 'job_uuid' => 'leased-job']);
        $this->event('processed', ['processed_at' => now(), 'worker_result' => 'success']);
        $this->event('failed', ['processed_at' => now(), 'worker_result' => 'failed']);
        $this->event('ambiguous', ['processed_at' => now(), 'worker_result' => 'needs_review']);
        $this->event('not-due', ['available_at' => now()->addHour()]);
        $this->event('other-type', ['event_type' => 'send_email']);

        $this->artisan('outbox:dispatch --limit=50')->assertSuccessful();

        Bus::assertDispatchedTimes(ProcessVoucherFulfillmentJob::class, 1);
        Bus::assertDispatched(
            ProcessVoucherFulfillmentJob::class,
            fn (ProcessVoucherFulfillmentJob $job): bool => $job->outboxEventId === $pending->id,
        );

        $this->assertNotNull($pending->fresh()->dispatched_at);
        $this->assertNotNull($pending->fresh()->job_uuid);
    }

    private function event(string $suffix, array $overrides = []): OutboxEvent
    {
        return OutboxEvent::query()->create(array_merge([
            'event_type' => 'fulfill_voucher',
            'idempotency_key' => 'test-'.$suffix,
            'aggregate_type' => 'test',
            'aggregate_id' => 1,
            'payload_json' => ['order_id' => 1, 'order_item_id' => 1],
            'correlation_id' => 'corr-'.$suffix,
            'available_at' => now()->subMinute(),
        ], $overrides));
    }
}
