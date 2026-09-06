<?php

namespace App\Jobs;

use App\Enums\AuditAction;
use App\Models\AutomationFlow;
use App\Models\AutomationRun;
use App\Models\AutomationStep;
use App\Models\Contact;
use App\Models\StifinOperation;
use App\Services\Audit\AuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class AutomationTriggerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly int $automationFlowId,
        public readonly int $contactId,
        public readonly string $triggerType,
        public readonly array $triggerPayload = [],
    ) {
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $flow = AutomationFlow::query()
                ->where('id', $this->automationFlowId)
                ->where('status', \App\Enums\AutomationFlowStatus::Active)
                ->first();

            if (! $flow) {
                return;
            }

            $contact = Contact::query()->find($this->contactId);
            if (! $contact) {
                return;
            }

            $runExists = AutomationRun::query()
                ->where('automation_flow_id', $flow->id)
                ->where('contact_id', $contact->id)
                ->where('status', \App\Enums\AutomationFlowStatus::Active)
                ->exists();

            if ($runExists) {
                return;
            }

            $run = AutomationRun::query()->create([
                'automation_flow_id' => $flow->id,
                'contact_id' => $contact->id,
                'trigger_type' => $this->triggerType,
                'trigger_payload_json' => $this->triggerPayload,
                'status' => \App\Enums\AutomationFlowStatus::Active,
                'current_step_id' => null,
                'started_at' => now(),
            ]);

            $steps = AutomationStep::query()
                ->where('automation_flow_id', $flow->id)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($steps as $step) {
                $this->executeStep($step, $contact, $run);
            }

            $run->status = \App\Enums\AutomationFlowStatus::Active;
            $run->completed_at = now();
            $run->save();

            AuditService::record(
                action: AuditAction::from('automation.run_executed') ?? 'automation.run_executed',
                subject: $run,
                after: [
                    'flow_id' => $flow->id,
                    'flow_name' => $flow->name,
                    'contact_id' => $contact->id,
                    'step_count' => $steps->count(),
                ],
            );
        });
    }

    private function executeStep(AutomationStep $step, Contact $contact, AutomationRun $run): void
    {
        switch ($step->action_type) {
            case 'send_email':
            case 'send_whatsapp':
                $this->sendMessageAction($step, $contact);
                break;
            case 'wait':
                break;
            case 'update_contact':
                $this->updateContactAction($step, $contact);
                break;
            case 'add_tag':
                break;
        }
    }

    private function sendMessageAction(AutomationStep $step, Contact $contact): void
    {
    }

    private function updateContactAction(AutomationStep $step, Contact $contact): void
    {
        $payload = $step->config_json ?? [];
        if (isset($payload['status'])) {
            $contact->status = $payload['status'];
            $contact->save();
        }
    }

    public function failed(Throwable $exception): void
    {
        DB::transaction(function () use ($exception) {
            StifinOperation::query()->create([
                'operation_type' => 'automation.trigger_failed',
                'request_reference' => 'automation-' . $this->automationFlowId . '-contact-' . $this->contactId,
                'request_hash' => hash('sha256', 'automation-fail-' . $this->automationFlowId . '-' . $this->contactId . '-' . now()->toIso8601String()),
                'redacted_payload_json' => [
                    'automation_flow_id' => $this->automationFlowId,
                    'contact_id' => $this->contactId,
                    'trigger_type' => $this->triggerType,
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
