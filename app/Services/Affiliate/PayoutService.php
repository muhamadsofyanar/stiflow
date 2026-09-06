<?php

namespace App\Services\Affiliate;

use App\Enums\CommissionEntryStatus;
use App\Enums\PayoutStatus;
use App\Enums\AuditAction;
use App\Models\CommissionEntry;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\PaymentProof;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PayoutService
{
    public function preparePayout(
        int $commissionPlanId,
        User $preparer,
        array $criteria = [],
    ): Payout {
        if (! $preparer->isStaffOrAbove()) {
            throw new InvalidArgumentException('Hanya staff/admin yang dapat menyiapkan payout.');
        }

        return DB::transaction(function () use ($commissionPlanId, $preparer, $criteria) {
            $minAmount = $criteria['minimum_total'] ?? null;
            $periodStart = $criteria['period_start'] ?? null;
            $periodEnd = $criteria['period_end'] ?? null;

            $query = CommissionEntry::query()
                ->where('commission_plan_id', $commissionPlanId)
                ->where('status', CommissionEntryStatus::Payable)
                ->whereNull('payout_id')
                ->lockForUpdate();

            if ($periodStart) {
                $query->whereDate('earned_at', '>=', $periodStart);
            }
            if ($periodEnd) {
                $query->whereDate('earned_at', '<=', $periodEnd);
            }

            $entries = $query->orderBy('beneficiary_promoter_profile_id', 'asc')->orderBy('id', 'asc')->get();

            if ($entries->isEmpty()) {
                throw new RuntimeException('Tidak ada komisi yang dapat dibayarkan.');
            }

            $batchNumber = 'PAY-' . date('Ymd') . '-' . Str::random(6);
            $totalAmount = (float) $entries->sum('amount');

            if ($minAmount !== null && $totalAmount < (float) $minAmount) {
                throw new RuntimeException("Total payout {$totalAmount} kurang dari minimum yang ditentukan.");
            }

            $payout = Payout::query()->create([
                'batch_number' => $batchNumber,
                'commission_plan_id' => $commissionPlanId,
                'status' => PayoutStatus::Draft,
                'total_amount' => $totalAmount,
                'entry_count' => $entries->count(),
                'prepared_by_user_id' => $preparer->id,
                'approved_by_user_id' => null,
                'paid_by_user_id' => null,
                'payment_method' => $criteria['payment_method'] ?? 'bank_transfer',
                'bank_name' => $criteria['bank_name'] ?? null,
                'bank_account_number' => $criteria['bank_account_number'] ?? null,
                'bank_account_name' => $criteria['bank_account_name'] ?? null,
                'notes' => $criteria['notes'] ?? null,
                'admin_rejection_notes' => null,
                'locked_at' => null,
                'approved_at' => null,
                'paid_at' => null,
                'reversed_at' => null,
                'period_start_date' => $periodStart,
                'period_end_date' => $periodEnd,
            ]);

            $items = [];
            $now = now();
            foreach ($entries as $entry) {
                $items[] = [
                    'payout_id' => $payout->id,
                    'commission_entry_id' => $entry->id,
                    'beneficiary_promoter_profile_id' => $entry->beneficiary_promoter_profile_id,
                    'amount' => $entry->amount,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($items)) {
                PayoutItem::query()->insert($items);
            }

            CommissionEntry::query()
                ->whereIn('id', $entries->pluck('id')->all())
                ->update([
                    'payout_id' => $payout->id,
                    'status' => CommissionEntryStatus::Locked,
                    'locked_at' => $now,
                ]);

            AuditService::record(
                action: AuditAction::PaymentApproved ?? 'payout.prepared',
                subject: $payout,
                after: [
                    'batch_number' => $payout->batch_number,
                    'total_amount' => (float) $payout->total_amount,
                    'entry_count' => $payout->entry_count,
                ],
                actor: $preparer,
            );

            return $payout->load('items', 'commissionEntries');
        }, 3);
    }

    public function approvePayout(Payout $payout, User $approver, string $notes = ''): Payout
    {
        if (! $approver->isStaffOrAbove()) {
            throw new InvalidArgumentException('Hanya staff/admin yang dapat menyetujui payout.');
        }

        return DB::transaction(function () use ($payout, $approver, $notes) {
            $payout = Payout::query()->where('id', $payout->id)->lockForUpdate()->firstOrFail();

            if ($payout->status !== PayoutStatus::Draft) {
                throw new RuntimeException('Payout hanya dapat disetujui dalam status draft.');
            }

            $payout->status = PayoutStatus::Locked;
            $payout->locked_at = now();
            $payout->approved_by_user_id = $approver->id;
            $payout->approved_at = now();
            $payout->notes = ($payout->notes ? $payout->notes . "\n" : '') . $notes;
            $payout->save();

            AuditService::record(
                action: AuditAction::PaymentApproved ?? 'payout.approved',
                subject: $payout,
                after: [
                    'approver_id' => $approver->id,
                    'notes' => $notes,
                ],
                actor: $approver,
            );

            return $payout;
        }, 3);
    }

    public function markPaid(
        Payout $payout,
        User $payer,
        ?UploadedFile $proofFile = null,
        array $proofAdditional = [],
    ): Payout {
        if (! $payer->isStaffOrAbove()) {
            throw new InvalidArgumentException('Hanya staff/admin yang dapat menandai payout dibayar.');
        }

        return DB::transaction(function () use ($payout, $payer, $proofFile, $proofAdditional) {
            $payout = Payout::query()->where('id', $payout->id)->lockForUpdate()->firstOrFail();

            if (! in_array($payout->status, [PayoutStatus::Locked, PayoutStatus::Approved], true)) {
                throw new RuntimeException('Payout harus dalam status locked/approved sebelum dibayar.');
            }

            if ($proofFile) {
                $filename = 'payout_proof_' . $payout->batch_number . '_' . Str::random(10) . '.' . $proofFile->getClientOriginalExtension();
                $storedPath = $proofFile->storeAs('payout_proofs', $filename, 'private');

                $proof = PaymentProof::query()->create([
                    'payment_attempt_id' => null,
                    'file_path' => $storedPath,
                    'original_filename' => $proofFile->getClientOriginalName(),
                    'mime_type' => $proofFile->getMimeType(),
                    'file_size' => $proofFile->getSize(),
                    'bank_name' => $proofAdditional['bank_name'] ?? null,
                    'sender_name' => $proofAdditional['sender_name'] ?? null,
                    'sender_bank' => $proofAdditional['sender_bank'] ?? null,
                    'transfer_amount' => $proofAdditional['transfer_amount'] ?? $payout->total_amount,
                    'transfer_time' => $proofAdditional['transfer_time'] ?? now(),
                    'review_status' => 'approved',
                ]);

                $payout->paymentProofs()->save($proof);
            }

            $payout->status = PayoutStatus::Paid;
            $payout->paid_at = now();
            $payout->paid_by_user_id = $payer->id;
            $payout->save();

            CommissionEntry::query()
                ->where('payout_id', $payout->id)
                ->update([
                    'status' => CommissionEntryStatus::Paid,
                    'paid_at' => now(),
                ]);

            AuditService::record(
                action: AuditAction::PaymentApproved ?? 'payout.paid',
                subject: $payout,
                after: [
                    'paid_by' => $payer->id,
                    'total_amount' => (float) $payout->total_amount,
                ],
                actor: $payer,
            );

            return $payout;
        }, 3);
    }

    public function isLocked(Payout $payout): bool
    {
        if ($payout->locked_at !== null) {
            return true;
        }

        return in_array($payout->status, [
            PayoutStatus::Locked,
            PayoutStatus::Approved,
            PayoutStatus::Paid,
            PayoutStatus::Reversed,
        ], true);
    }
}
