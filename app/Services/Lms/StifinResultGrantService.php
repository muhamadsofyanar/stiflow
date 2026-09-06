<?php

namespace App\Services\Lms;

use App\Enums\AuditAction;
use App\Models\Contact;
use App\Models\StifinResult;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StifinResultGrantService
{
    public function createResult(array $payload, User $uploader): StifinResult
    {
        $contactId = $payload['contact_id'] ?? null;
        if ($contactId === null) {
            throw new InvalidArgumentException('contact_id wajib diisi.');
        }

        $contact = Contact::query()->find($contactId);
        if (! $contact) {
            throw new InvalidArgumentException('Contact tidak ditemukan.');
        }

        $promoterOwnerProfileId = $payload['promoter_owner_profile_id'] ?? null;
        if ($promoterOwnerProfileId === null) {
            $promoterOwnerProfileId = $contact->owner_promoter_profile_id;
        }

        $memberUserId = $payload['member_user_id'] ?? $contact->user_linked_id;

        return DB::transaction(function () use ($payload, $uploader, $contact, $promoterOwnerProfileId, $memberUserId) {
            $result = StifinResult::query()->create([
                'contact_id' => $contact->id,
                'member_user_id' => $memberUserId,
                'uploaded_by_user_id' => $uploader->id,
                'promoter_owner_profile_id' => $promoterOwnerProfileId,
                'name' => $payload['name'] ?? $contact->full_name,
                'result_type' => $payload['result_type'] ?? 'stifin_pro',
                'main_result_json' => $payload['main_result_json'] ?? null,
                'elements_json' => $payload['elements_json'] ?? null,
                'summary_text' => $payload['summary_text'] ?? null,
                'pdf_file_path' => $payload['pdf_file_path'] ?? null,
                'source_file_name' => $payload['source_file_name'] ?? null,
                'report_batch_number' => $payload['report_batch_number'] ?? null,
                'test_taken_date' => $payload['test_taken_date'] ?? null,
                'valid_until_date' => $payload['valid_until_date'] ?? null,
                'is_sensitive_locked' => $payload['is_sensitive_locked'] ?? false,
                'access_granted_by_user_id' => $uploader->id,
                'generated_at' => now(),
                'linked_course_ids_json' => $payload['linked_course_ids_json'] ?? null,
            ]);

            AuditService::record(
                action: AuditAction::ProductCreated ?? 'stifin_result.created',
                subject: $result,
                after: [
                    'contact_id' => $contact->id,
                    'promoter_owner_profile_id' => $promoterOwnerProfileId,
                    'member_user_id' => $memberUserId,
                    'result_type' => $result->result_type,
                ],
                actor: $uploader,
            );

            return $result;
        });
    }

    public function grantViewAccess(StifinResult $result, User $grantee, User $grantor): StifinResult
    {
        if (! $grantor->isStaffOrAbove() && (int) $result->promoter_owner_profile_id !== (int) ($grantor->promoterProfile?->id ?? -1)) {
            throw new InvalidArgumentException('Anda tidak berwenang memberikan akses hasil ini.');
        }

        return DB::transaction(function () use ($result, $grantee, $grantor) {
            $result->access_granted_by_user_id = $grantor->id;
            $result->save();

            AuditService::record(
                action: AuditAction::ProductCreated ?? 'stifin_result.access_granted',
                subject: $result,
                after: [
                    'grantee_user_id' => $grantee->id,
                    'grantor_user_id' => $grantor->id,
                ],
                actor: $grantor,
            );

            return $result;
        });
    }
}
