<?php

namespace App\Services\Communication;

use App\Enums\AuditAction;
use App\Enums\CampaignStatus;
use App\Enums\ContactStatus;
use App\Enums\MessageChannel;
use App\Enums\SubscriptionStatus;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\ContactSubscription;
use App\Models\MessageTemplate;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TemplatePlaceholderService
{
    public function render(string $template, array $data = []): string
    {
        if ($template === '') {
            return '';
        }

        $output = $template;

        $output = preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*)\s*\}\}/', function ($matches) use ($data) {
            $key = $matches[1];
            $value = $this->resolveNestedValue($key, $data);

            if ($value === null) {
                return '';
            }

            if (is_scalar($value)) {
                return (string) $value;
            }

            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            if (is_object($value) && method_exists($value, '__toString')) {
                return (string) $value;
            }

            return '';
        }, $output);

        return $output;
    }

    public function renderTemplate(MessageTemplate $template, array $data = []): array
    {
        return [
            'subject' => $this->render($template->subject_line ?? '', $data),
            'body' => $this->render($template->content_body ?? '', $data),
        ];
    }

    public function getPlaceholders(string $template): array
    {
        if ($template === '') {
            return [];
        }

        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*)\s*\}\}/', $template, $matches);

        $placeholders = $matches[1] ?? [];

        return array_values(array_unique($placeholders));
    }

    public function buildContactPlaceholderData(Contact $contact, array $extra = []): array
    {
        $data = [
            'contact' => [
                'id' => $contact->id,
                'full_name' => $contact->full_name,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'whatsapp' => $contact->whatsapp,
                'address' => $contact->address,
                'city' => $contact->city,
                'province' => $contact->province,
                'birthday' => $contact->birthday?->toDateString(),
                'occupation' => $contact->occupation,
                'status' => $contact->status?->value,
            ],
        ];

        if (! empty($contact->meta) && is_array($contact->meta)) {
            foreach ($contact->meta as $k => $v) {
                $data['contact_meta'][$k] = $v;
            }
        }

        foreach ($extra as $k => $v) {
            $data[$k] = $v;
        }

        return $data;
    }

    public function sendCampaignChunk(Campaign $campaign, array $contactIds): array
    {
        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'skipped_unsubscribe' => 0];

        $template = $campaign->messageTemplate;
        $bodyTemplate = $campaign->template_snapshot_body ?? ($template?->content_body ?? '');
        $subjectTemplate = $campaign->template_snapshot_subject ?? ($template?->subject_line ?? '');
        $placeholderValues = $campaign->placeholders_values_json ?? [];

        $channelValue = $campaign->channel?->value ?? MessageChannel::Email->value;
        $unsubscribed = $this->getUnsubscribedContactIds($contactIds, $channelValue);

        DB::transaction(function () use ($campaign, $contactIds, $bodyTemplate, $subjectTemplate, $placeholderValues, $channelValue, $unsubscribed, &$results) {
            foreach ($contactIds as $contactId) {
                $recipient = CampaignRecipient::query()
                    ->where('campaign_id', $campaign->id)
                    ->where('contact_id', $contactId)
                    ->first();

                if (! $recipient) {
                    $results['skipped']++;
                    continue;
                }

                if (in_array($contactId, $unsubscribed, true)) {
                    $recipient->status = \App\Enums\CampaignRecipientStatus::Skipped;
                    $recipient->delivery_note = 'unsubscribed_' . $channelValue;
                    $recipient->save();
                    $results['skipped_unsubscribe']++;
                    continue;
                }

                $contact = Contact::query()->find($contactId);
                if (! $contact) {
                    $recipient->status = \App\Enums\CampaignRecipientStatus::Failed;
                    $recipient->delivery_note = 'contact_not_found';
                    $recipient->save();
                    $results['failed']++;
                    continue;
                }

                $mergeData = $this->buildContactPlaceholderData($contact, $placeholderValues);
                $body = $this->render($bodyTemplate, $mergeData);
                $subject = $this->render($subjectTemplate, $mergeData);

                $recipient->resolved_subject = $subject;
                $recipient->resolved_body = mb_substr($body, 0, 16000);
                $recipient->status = \App\Enums\CampaignRecipientStatus::Queued;
                $recipient->queued_at = now();
                $recipient->save();

                SendCampaignMessageJob::dispatch($campaign->id, $recipient->id)
                    ->delay(now()->addSeconds(rand(0, 60)));

                $results['sent']++;
            }
        });

        AuditService::record(
            action: AuditAction::ProductCreated ?? 'campaign.chunk_sent',
            subject: $campaign,
            after: [
                'contact_count' => count($contactIds),
                'sent' => $results['sent'],
                'failed' => $results['failed'],
                'skipped_unsubscribe' => $results['skipped_unsubscribe'],
            ],
        );

        return $results;
    }

    private function getUnsubscribedContactIds(array $contactIds, string $channel): array
    {
        if (empty($contactIds)) {
            return [];
        }

        $unsubscribedFromStatus = ContactSubscription::query()
            ->whereIn('contact_id', $contactIds)
            ->where('channel', $channel)
            ->where(function ($q) {
                $q->where('status', SubscriptionStatus::Unsubscribed)
                    ->orWhereNotNull('unsubscribed_at')
                    ->orWhere('allow_marketing', false);
            })
            ->pluck('contact_id')
            ->all();

        return array_values(array_unique($unsubscribedFromStatus));
    }

    private function resolveNestedValue(string $key, array $data): mixed
    {
        if (strpos($key, '.') === false) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }

            return null;
        }

        $parts = explode('.', $key);
        $current = $data;

        foreach ($parts as $part) {
            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
            } elseif (is_object($current) && isset($current->{$part})) {
                $current = $current->{$part};
            } else {
                return null;
            }
        }

        return $current;
    }
}
