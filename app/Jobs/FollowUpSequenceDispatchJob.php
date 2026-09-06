<?php

namespace App\Jobs;

use App\Enums\AutomationTriggerType;
use App\Enums\FollowUpStatus;
use App\Models\AutomationFlow;
use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FollowUpSequenceDispatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public array $sequenceDays = [1, 3, 7, 14, 30];

    public function __construct(
        public readonly int $contactId,
        public readonly string $triggerType = AutomationTriggerType::LeadCreated->value,
        public readonly array $triggerPayload = [],
    ) {
    }

    public function handle(): void
    {
        $contact = Contact::query()->find($this->contactId);
        if (! $contact) {
            Log::warning("FollowUpSequenceDispatchJob: Contact {$this->contactId} tidak ditemukan");
            return;
        }

        $flows = AutomationFlow::query()
            ->where(function ($q) {
                $q->where('trigger_type', $this->triggerType)
                    ->orWhere('trigger_type', AutomationTriggerType::LeadCreated->value)
                    ->orWhere('trigger_type', 'LIKE', '%FollowUp%');
            })
            ->where('status', \App\Enums\AutomationFlowStatus::Active->value)
            ->with('steps')
            ->get();

        if ($flows->isEmpty()) {
            $this->dispatchDefaultSequence($contact);
            return;
        }

        foreach ($flows as $flow) {
            AutomationTriggerJob::dispatch(
                $flow->id,
                $contact->id,
                $this->triggerType,
                $this->triggerPayload
            );
        }
    }

    private function dispatchDefaultSequence(Contact $contact): void
    {
        $brand = \App\Models\BrandSetting::query()->first();
        $brandName = $brand?->brand_name ?? config('app.name', 'STIFLOW');
        $placeholders = [
            '{{first_name}}' => $contact->first_name ?? ($contact->name ?? 'Teman'),
            '{{last_name}}' => $contact->last_name ?? '',
            '{{brand_name}}' => $brandName,
            '{{email}}' => $contact->email ?? '',
            '{{phone}}' => $contact->phone ?? '',
            '{{member_area_url}}' => route('member.dashboard'),
            '{{wa_group_link}}' => $brand?->whatsapp_group_url ?? 'https://wa.me/6281234567890',
            '{{referral_link}}' => method_exists($contact, 'owner') && $contact->owner
                ? ($contact->owner->referralLinks()->first()?->getShortUrl() ?? url('/'))
                : url('/'),
        ];

        foreach ($this->sequenceDays as $day) {
            $isActive = cache("followup_stage_{$day}_active") !== false;
            if (! $isActive) {
                continue;
            }

            $delayMinutes = $day * 24 * 60;

            try {
                $statusEnum = FollowUpStatus::Scheduled;
                DB::table('follow_up_items')->insertOrIgnore([
                    'contact_id' => $contact->id,
                    'stage_day' => $day,
                    'scheduled_at' => now()->addMinutes($delayMinutes),
                    'status' => $statusEnum->value,
                    'trigger_type' => $this->triggerType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable) {
            }

            if (class_exists(CampaignSendJob::class) || class_exists(CampaignDispatchJob::class)) {
                $this->dispatchCampaignFor($contact, $day, $delayMinutes, $placeholders);
            }
        }
    }

    private function dispatchCampaignFor(Contact $contact, int $day, int $delayMinutes, array $placeholders): void
    {
        $jobClass = class_exists(\App\Jobs\CampaignDispatchJob::class)
            ? \App\Jobs\CampaignDispatchJob::class
            : null;

        if (! $jobClass) {
            Log::info("FollowUp stage D+{$day} dijadwalkan untuk contact {$contact->id} delay {$delayMinutes} menit");
            return;
        }

        $payload = [
            'followup_stage' => $day,
            'placeholders' => $placeholders,
            'contact_ids' => [$contact->id],
            'trigger_type' => $this->triggerType,
        ];

        try {
            if (method_exists($jobClass, 'dispatch')) {
                $jobInstance = new $jobClass(0, $payload);
                if (method_exists($jobInstance, 'delay')) {
                    $jobClass::dispatch(0, $payload)->delay(now()->addMinutes($delayMinutes));
                } else {
                    $jobClass::dispatch(0, $payload);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal dispatch FollowUp stage D+{$day}: " . $e->getMessage());
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('FollowUpSequenceDispatchJob gagal: ' . $exception->getMessage(), [
            'contact_id' => $this->contactId,
            'trigger_type' => $this->triggerType,
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
