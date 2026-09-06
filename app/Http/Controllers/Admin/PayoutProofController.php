<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayoutStatus;
use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\Affiliate\PayoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayoutProofController extends Controller
{
    public function __construct(
        private readonly PayoutService $payoutService,
    ) {
        $this->middleware('can:payouts.approve');
    }

    public function showUploadForm(Payout $payout): View
    {
        return view('admin.payouts.upload-proof', compact('payout'));
    }

    public function uploadProof(Request $request, Payout $payout): RedirectResponse
    {
        $request->validate([
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'bank_name' => 'nullable|string|max:100',
            'sender_name' => 'nullable|string|max:150',
            'sender_bank' => 'nullable|string|max:100',
            'transfer_amount' => 'required|numeric|min:0',
            'transfer_time' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        if (! in_array($payout->status, [PayoutStatus::Locked, PayoutStatus::Approved], true)) {
            return redirect()->back()->with('error', 'Payout harus dalam status Locked/Approved untuk diunggah bukti.');
        }

        try {
            $proofFile = $request->file('proof_file');

            $proofAdditional = [
                'bank_name' => $request->input('bank_name'),
                'sender_name' => $request->input('sender_name'),
                'sender_bank' => $request->input('sender_bank'),
                'transfer_amount' => $request->input('transfer_amount'),
                'transfer_time' => $request->input('transfer_time'),
            ];

            $payer = $request->user();
            $this->payoutService->markPaid($payout, $payer, $proofFile, $proofAdditional);

            return redirect()->route('admin.payouts.show', $payout)
                ->with('success', 'Bukti transfer berhasil diunggah dan payout ditandai Paid.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengunggah bukti: ' . $e->getMessage());
        }
    }

    public function downloadProof(Payout $payout)
    {
        $proof = $payout->paymentProofs()->latest()->first();

        if (! $proof || ! Storage::disk('private')->exists($proof->file_path)) {
            abort(404, 'Bukti pembayaran tidak ditemukan.');
        }

        return Storage::disk('private')->download($proof->file_path, $proof->original_filename);
    }
}
