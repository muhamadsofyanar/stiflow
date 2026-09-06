<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoterProfile;
use App\Services\Promoter\PromoterVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PromotorVerifyController extends Controller
{
    public function verify(
        Request $request,
        PromoterProfile $profile,
        PromoterVerificationService $verificationService,
    ): RedirectResponse {
        try {
            $verificationService->verify(
                profile: $profile,
                actor: $request->user(),
                notes: $request->string('notes')->trim()->toString() ?: null,
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['stifin_code' => $exception->getMessage()]);
        }

        return back()->with('status', 'Promotor berhasil diverifikasi.');
    }
}
