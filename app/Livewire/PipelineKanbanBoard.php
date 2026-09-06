<?php

namespace App\Livewire;

use App\Models\Contact;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class PipelineKanbanBoard extends Component
{
    public Pipeline|int|null $pipeline = null;

    public Collection $stages;

    public array $stageContactIds = [];

    public function mount(Pipeline $pipeline = null): void
    {
        $this->pipeline = $pipeline ?: Pipeline::query()->where('is_active', true)->first();

        if ($this->pipeline) {
            $this->stages = $this->pipeline->stages()
                ->with(['contacts' => function ($q) {
                    if (auth()->user()?->isPromotor()) {
                        $profileId = auth()->user()->promoterProfile?->id;
                        $q->where(function ($sub) use ($profileId) {
                            $sub->where('owner_promoter_profile_id', $profileId)
                                ->orWhere('referred_by_promoter_profile_id', $profileId);
                        });
                    }
                    $q->with('ownerPromoterProfile');
                }])
                ->orderBy('position')
                ->get();
        } else {
            $this->stages = collect();
        }

        foreach ($this->stages as $stage) {
            $this->stageContactIds[$stage->id] = $stage->contacts->pluck('id')->toArray();
        }
    }

    public function moveContact(int $contactId, int $fromStageId, int $toStageId, int $newPosition): void
    {
        $contact = Contact::query()->findOrFail($contactId);

        if (auth()->user()?->isPromotor()) {
            $profileId = auth()->user()->promoterProfile?->id;
            abort_unless(
                $contact->owner_promoter_profile_id === $profileId || $contact->referred_by_promoter_profile_id === $profileId,
                403
            );
        }

        $contact->update([
            'stage_id' => $toStageId,
            'pipeline_id' => $this->pipeline?->id,
        ]);

        unset($this->stageContactIds[$fromStageId][$contactId]);
        $this->stageContactIds[$toStageId][$newPosition] = $contactId;
    }

    public function render()
    {
        return view('livewire.pipeline-kanban-board', [
            'pipeline' => $this->pipeline,
            'stages' => $this->stages,
        ]);
    }
}
