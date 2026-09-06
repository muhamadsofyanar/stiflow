<?php

namespace App\Services\Affiliate;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\PromoterProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PayoutExportService
{
    public function exportToCsv(Payout $payout, array $options = []): array
    {
        $items = PayoutItem::query()
            ->where('payout_id', $payout->id)
            ->with(['commissionEntry.beneficiaryPromoterProfile', 'commissionEntry.beneficiaryPromoterProfile.user'])
            ->get();

        $rows = [];
        $headers = [
            'No',
            'Payout Batch',
            'Beneficiary Name',
            'Stifin Code',
            'Bank Name',
            'Account Number',
            'Account Holder',
            'Amount (IDR)',
            'Commission Entry ID',
            'Reference',
            'Status',
        ];

        $rows[] = implode(',', $headers);

        $idx = 1;
        $grandTotal = 0;
        foreach ($items as $item) {
            $entry = $item->commissionEntry;
            $profile = $entry?->beneficiaryPromoterProfile;
            $user = $profile?->user;

            $bankName = $profile?->bank_name ?? $payout->bank_name ?? '';
            $accountNumber = $profile?->bank_account_number ?? $payout->bank_account_number ?? '';
            $accountHolder = $profile?->bank_account_name ?? $payout->bank_account_name ?? ($user?->name ?? '');

            $amount = (float) ($item->amount ?? 0);
            $grandTotal += $amount;

            $row = [
                $idx++,
                $payout->batch_number,
                $user?->name ?? ($profile?->stifin_code ?? ''),
                $profile?->stifin_code ?? '',
                $this->csvEscape($bankName),
                $this->csvEscape($accountNumber),
                $this->csvEscape($accountHolder),
                number_format($amount, 2, '.', ''),
                $entry?->id ?? '',
                $entry?->reference_id ?? '',
                $payout->status->value ?? 'unknown',
            ];

            $rows[] = implode(',', $row);
        }

        $summaryRow = [
            '',
            'TOTAL',
            '',
            '',
            '',
            '',
            '',
            number_format($grandTotal, 2, '.', ''),
            '',
            '',
            '',
        ];
        $rows[] = implode(',', $summaryRow);

        $csvContent = implode("\n", $rows);

        $filename = 'payout_' . $payout->batch_number . '_' . date('Ymd_His') . '.csv';
        $relativePath = 'payout_exports/' . $filename;

        Storage::disk('private')->put($relativePath, $csvContent);

        return [
            'filename' => $filename,
            'storage_path' => $relativePath,
            'item_count' => $items->count(),
            'grand_total' => round($grandTotal, 2),
            'download_url' => $options['include_url'] ?? false
                ? route('admin.payouts.download-export', ['payout' => $payout->id, 'file' => $filename])
                : null,
        ];
    }

    public function exportByPromoter(Payout $payout, int $promoterProfileId, array $options = []): array
    {
        $profile = PromoterProfile::query()->find($promoterProfileId);
        if (! $profile) {
            throw new \InvalidArgumentException('Promoter profile tidak ditemukan.');
        }

        $items = PayoutItem::query()
            ->where('payout_id', $payout->id)
            ->whereHas('commissionEntry', function ($q) use ($promoterProfileId) {
                $q->where('beneficiary_promoter_profile_id', $promoterProfileId);
            })
            ->with(['commissionEntry.order', 'commissionEntry.orderItem'])
            ->get();

        if ($items->isEmpty()) {
            throw new \RuntimeException('Tidak ada item payout untuk promoter ini.');
        }

        $rows = [];
        $headers = [
            'No',
            'Order Number',
            'Tanggal Order',
            'Product',
            'Level Komisi',
            'Rate',
            'Amount (IDR)',
            'Status',
        ];
        $rows[] = implode(',', $headers);

        $idx = 1;
        $grandTotal = 0;
        foreach ($items as $item) {
            $entry = $item->commissionEntry;
            $order = $entry?->order;
            $orderItem = $entry?->orderItem;
            $snapshot = $orderItem?->product_snapshot_json ?? [];

            $amount = (float) ($item->amount ?? 0);
            $grandTotal += $amount;

            $row = [
                $idx++,
                $order?->number ?? '',
                $order?->paid_at ? $order->paid_at->format('Y-m-d') : '',
                $this->csvEscape($snapshot['name'] ?? ''),
                $entry?->level ?? '',
                $entry?->rate_value ? ($entry->rate_type === 'percentage' ? "{$entry->rate_value}%" : $entry->rate_value) : '',
                number_format($amount, 2, '.', ''),
                $entry?->status?->value ?? 'unknown',
            ];

            $rows[] = implode(',', $row);
        }

        $summaryRow = ['', '', '', '', '', 'TOTAL', number_format($grandTotal, 2, '.', ''), ''];
        $rows[] = implode(',', $summaryRow);

        $csvContent = implode("\n", $rows);

        $filename = 'payout_' . $payout->batch_number . '_promoter_' . $profile->stifin_code . '_' . date('Ymd_His') . '.csv';
        $relativePath = 'payout_exports/' . $filename;

        Storage::disk('private')->put($relativePath, $csvContent);

        return [
            'filename' => $filename,
            'storage_path' => $relativePath,
            'item_count' => $items->count(),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    public function getExportDownload(Payout $payout, string $filename)
    {
        $relativePath = 'payout_exports/' . basename($filename);

        if (! Storage::disk('private')->exists($relativePath)) {
            abort(404, 'File export tidak ditemukan.');
        }

        return Storage::disk('private')->download($relativePath, $filename);
    }

    public function getBulkTransferTemplate(Payout $payout): array
    {
        $items = PayoutItem::query()
            ->where('payout_id', $payout->id)
            ->with(['commissionEntry.beneficiaryPromoterProfile', 'commissionEntry.beneficiaryPromoterProfile.user'])
            ->get();

        $rows = [];
        $headers = [
            'beneficiary_code',
            'beneficiary_name',
            'bank_code',
            'bank_name',
            'account_number',
            'amount',
            'description',
            'email',
        ];
        $rows[] = implode(',', $headers);

        foreach ($items as $item) {
            $entry = $item->commissionEntry;
            $profile = $entry?->beneficiaryPromoterProfile;
            $user = $profile?->user;

            $row = [
                $profile?->stifin_code ?? '',
                $this->csvEscape($user?->name ?? ''),
                $profile?->bank_code ?? '',
                $this->csvEscape($profile?->bank_name ?? ($payout->bank_name ?? '')),
                $profile?->bank_account_number ?? ($payout->bank_account_number ?? ''),
                number_format((float) ($item->amount ?? 0), 2, '.', ''),
                $this->csvEscape("Komisi {$payout->batch_number}"),
                $user?->email ?? '',
            ];

            $rows[] = implode(',', $row);
        }

        $csvContent = implode("\n", $rows);

        $filename = 'bulk_transfer_' . $payout->batch_number . '_' . date('Ymd_His') . '.csv';
        $relativePath = 'payout_exports/' . $filename;

        Storage::disk('private')->put($relativePath, $csvContent);

        return [
            'filename' => $filename,
            'storage_path' => $relativePath,
            'item_count' => $items->count(),
        ];
    }

    private function csvEscape(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }
}
