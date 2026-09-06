<?php

namespace App\Livewire;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class CampaignScheduleButton extends Component
{
    public Campaign $campaign;

    public ?string $scheduledAt = null;

    public ?string $testRecipient = null;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
        $this->scheduledAt = $campaign->scheduled_at?->format('Y-m-d\TH:i');
    }

    public function schedule(): void
    {
        $this->validate([
            'scheduledAt' => 'required|date|after:now',
        ]);

        DB::transaction(function () {
            $this->campaign->update([
                'scheduled_at' => $this->scheduledAt,
                'status' => CampaignStatus::Scheduled,
            ]);
        });

        session()->flash('status', 'Kampanye dijadwalkan pada '.$this->scheduledAt);
    }

    public function sendTest(): void
    {
        $validator = Validator::make(['testRecipient' => $this->testRecipient], [
            'testRecipient' => 'required|email',
        ]);

        if ($validator->fails()) {
            $this->addError('testRecipient', 'Email penerima test tidak valid.');

            return;
        }

        DB::transaction(function () {
            \App\Models\OutboxEvent::query()->create([
                'event_type' => 'campaign.test',
                'aggregate_type' => 'campaign',
                'aggregate_id' => (string) $this->campaign->id,
                'payload_json' => [
                    'campaign_id' => $this->campaign->id,
                    'test_recipient' => $this->testRecipient,
                    'dispatched_at' => now()->toIso8601String(),
                ],
                'dispatched_at' => null,
            ]);
        });

        session()->flash('status', 'Test campaign dikirim ke '.$this->testRecipient);
    }

    public function render()
    {
        return view('livewire.campaign-schedule-button');
    }
}
