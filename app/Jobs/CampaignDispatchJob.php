<?php

namespace App\Jobs;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\ContactSubscription;
use App\Models\StifinOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class CampaignDispatchJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 600;

    public int $tries = 1;

    public int $timeout = 600;

    public bool $failOnTimeout = true;

    public function uniqueId(): string
    {
        return 'campaign-dispatch-' . now()->toDateString();
    }

    public function handle(): void
    {
        $now = now();

        $campaigns = Campaign::query()
            ->where('status', CampaignStatus::Scheduled)
            ->whereNotNull('schedule_send_at')
            ->where('schedule_send_at', '<=', $now)
            ->lockForUpdate()
            ->get();

        foreach ($campaigns as $campaign) {
            DB::transaction(function () use ($campaign, $now) {
                $campaign = Campaign::query()
                    ->where('id', $campaign->id)
                    ->lockForUpdate()
                    ->first();

                if (! $campaign || $campaign->status !== CampaignStatus::Scheduled) {
                    return;
                }

                $contactIds = $this->resolveAudienceContactIds($campaign);
                $contactIds = $this->excludeUnsubscribed($contactIds, $campaign->channel?->value ?? 'email');

                $recipients = [];
                foreach ($contactIds as $cid) {
                    $recipients[] = [
                        'campaign_id' => $campaign->id,
                        'contact_id' => $cid,
                        'status' => \App\Enums\CampaignRecipientStatus::Pending,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! empty($recipients)) {
                    CampaignRecipient::query()->insert($recipients);
                }

                $campaign->status = CampaignStatus::Sending;
                $campaign->sending_started_at = $now;
                $campaign->total_recipients = count($recipients);
                $campaign->save();

                foreach (array_chunk($contactIds, 100) as $chunkIdx => $chunk) {
                    SendCampaignChunkJob::dispatch($campaign->id, $chunk)
                        ->delay(now()->addSeconds($chunkIdx * 5));
                }
            });
        }
    }

    private function resolveAudienceContactIds(Campaign $campaign): array
    {
        $query = Contact::query()->select('contacts.id');

        if ($campaign->audience_type === 'segment' && $campaign->segment_id) {
            return [];
        }

        if ($campaign->audience_type === 'contact_list' && $campaign->contact_list_id) {
            $query->join('contact_list_members', 'contact_list_members.contact_id', '=', 'contacts.id')
                ->where('contact_list_members.contact_list_id', $campaign->contact_list_id);
        }

        return $query->pluck('contacts.id')->all();
    }

    private function excludeUnsubscribed(array $contactIds, string $channel): array
    {
        if (empty($contactIds)) {
            return [];
        }

        $unsubscribed = ContactSubscription::query()
            ->whereIn('contact_id', $contactIds)
            ->where('channel', $channel)
            ->where('is_subscribed', false)
            ->pluck('contact_id')
            ->all();

        return array_values(array_diff($contactIds, $unsubscribed));
    }

    public function failed(Throwable $exception): void
    {
        DB::transaction(function () use ($exception) {
            StifinOperation::query()->create([
                'operation_type' => 'campaign.dispatch_failed',
                'request_reference' => 'campaign-dispatch-' . now()->toDateString(),
                'request_hash' => hash('sha256', 'campaign-dispatch-fail-' . now()->toIso8601String()),
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
