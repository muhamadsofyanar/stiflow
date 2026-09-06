<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ReferralLink;
use App\Models\ReferralVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralLinkRedirectController extends Controller
{
    public function redirect(Request $request, ReferralLink $referralLink): RedirectResponse
    {
        if (! $referralLink->is_active) {
            abort(404, 'Referral link tidak aktif.');
        }

        if ($referralLink->expires_at && $referralLink->expires_at->isPast()) {
            abort(410, 'Referral link sudah kedaluwarsa.');
        }

        ReferralVisit::query()->create([
            'referral_link_id' => $referralLink->id,
            'promoter_profile_id' => $referralLink->promoter_profile_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'utm_source' => $referralLink->utm_source,
            'utm_medium' => $referralLink->utm_medium,
            'utm_campaign' => $referralLink->utm_campaign,
            'visited_at' => now(),
        ]);

        $referralLink->increment('total_visits');

        $cookie = cookie('stiflow_referral_slug', $referralLink->slug, 60 * 24 * 30);

        $destination = $referralLink->destination_path ?? '/';

        return redirect()->to($destination)->withCookie($cookie);
    }
}
