<?php

namespace App\Http\Controllers;

use App\Models\PaymentProof;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    public function download(PaymentProof $proof): StreamedResponse
    {
        Gate::authorize('download', $proof);

        if (! Storage::disk('private')->exists($proof->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('private')->download(
            $proof->file_path,
            'bukti-transfer-' . ($proof->paymentAttempt?->order?->number ?? $proof->id) . '-' . $proof->original_filename,
            ['Content-Type' => $proof->mime_type],
        );
    }
}
