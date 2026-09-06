<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AffiliateController extends Controller
{
    public function tree(): View
    {
        $user = auth()->user();
        $profileId = $user->promoterProfile?->id;

        $directReferrals = Referral::query()
            ->where('referrer_promoter_profile_id', $profileId)
            ->with(['referredContact', 'referredUser.promoterProfile'])
            ->latest()
            ->get();

        $levels = $this->buildLevels($profileId);

        return view('promotor.affiliate.tree', compact('directReferrals', 'levels'));
    }

    private function buildLevels($profileId, int $maxDepth = 3): array
    {
        $levels = [];
        $currentIds = [$profileId];

        for ($depth = 1; $depth <= $maxDepth; $depth++) {
            $refs = Referral::query()
                ->whereIn('referrer_promoter_profile_id', $currentIds)
                ->with(['referredContact', 'referredUser.promoterProfile'])
                ->get();

            $levels[$depth] = $refs;
            $currentIds = $refs->pluck('referred_promoter_profile_id')->filter()->values()->all();

            if (empty($currentIds)) {
                break;
            }
        }

        return $levels;
    }
}
