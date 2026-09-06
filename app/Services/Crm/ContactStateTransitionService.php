<?php

namespace App\Services\Crm;

use App\Enums\AuditAction;
use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Services\Audit\AuditService;
use InvalidArgumentException;

class ContactStateTransitionService
{
    private const VALID_TRANSITIONS = [
        ContactStatus::Prospect->value => [
            ContactStatus::Qualified->value,
            ContactStatus::Lost->value,
        ],
        ContactStatus::Qualified->value => [
            ContactStatus::Client->value,
            ContactStatus::Lost->value,
        ],
        ContactStatus::Client->value => [
            ContactStatus::Member->value,
            ContactStatus::Won->value,
        ],
        ContactStatus::Member->value => [
            ContactStatus::CandidatePromoter->value,
            ContactStatus::Won->value,
        ],
    ];

    public function canTransition(Contact $contact, ContactStatus $to): bool
    {
        $fromValue = $contact->status?->value ?? $contact->status;
        if ($fromValue instanceof ContactStatus) {
            $fromValue = $fromValue->value;
        }

        if (! isset(self::VALID_TRANSITIONS[$fromValue])) {
            return false;
        }

        return in_array($to->value, self::VALID_TRANSITIONS[$fromValue], true);
    }

    public function transition(Contact $contact, ContactStatus $to, array $metadata = []): Contact
    {
        $fromValue = $contact->status?->value ?? $contact->status;
        if ($fromValue instanceof ContactStatus) {
            $fromValue = $fromValue->value;
        }

        if ($fromValue === $to->value) {
            return $contact;
        }

        if (! $this->canTransition($contact, $to)) {
            throw new InvalidArgumentException(
                "Transisi status dari {$fromValue} ke {$to->value} tidak valid."
            );
        }

        $beforeStatus = $contact->status;
        $contact->status = $to;

        if ($to === ContactStatus::CandidatePromoter || $to === ContactStatus::Member || $to === ContactStatus::Client) {
            if ($contact->converted_at === null) {
                $contact->converted_at = now();
            }
        }

        $contact->save();

        AuditService::record(
            action: AuditAction::OrderStatusChanged ?? 'contact.status_changed',
            subject: $contact,
            before: ['status' => $beforeStatus?->value ?? $beforeStatus],
            after: ['status' => $to->value],
            metadata: $metadata,
        );

        return $contact->fresh();
    }

    public function toQualified(Contact $contact, array $metadata = []): Contact
    {
        return $this->transition($contact, ContactStatus::Qualified, $metadata);
    }

    public function toClient(Contact $contact, array $metadata = []): Contact
    {
        return $this->transition($contact, ContactStatus::Client, $metadata);
    }

    public function toMember(Contact $contact, array $metadata = []): Contact
    {
        return $this->transition($contact, ContactStatus::Member, $metadata);
    }

    public function toCandidatePromoter(Contact $contact, array $metadata = []): Contact
    {
        return $this->transition($contact, ContactStatus::CandidatePromoter, $metadata);
    }
}
