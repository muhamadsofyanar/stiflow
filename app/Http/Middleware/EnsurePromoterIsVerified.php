<?php

namespace App\Http\Middleware;

use App\Enums\PromoterVerificationStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePromoterIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        if (! $user->isPromotor()) {
            abort(403, 'Hanya promotor yang dapat mengakses area ini.');
        }

        $profile = $user->promoterProfile;
        if (! $profile || $profile->verification_status !== PromoterVerificationStatus::Verified) {
            abort(403, 'Akun promotor Anda belum diverifikasi admin. Hubungi admin untuk verifikasi.');
        }

        return $next($request);
    }
}
