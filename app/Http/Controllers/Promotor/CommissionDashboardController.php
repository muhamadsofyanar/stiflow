<?php

namespace App\Http\Controllers\Promotor;

use App\Enums\CommissionEntryStatus;
use App\Http\Controllers\Controller;
use App\Models\CommissionEntry;
use App\Models\PromoterProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CommissionDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('verified_promotor');
    }

    public function pending(Request $request): View
    {
        $profileId = $this->getCurrentProfileId();

        $commissions = CommissionEntry::query()
            ->where('beneficiary_promoter_profile_id', $profileId)
            ->where('status', CommissionEntryStatus::Pending)
            ->with(['order', 'orderItem', 'commissionRule'])
            ->latest('earned_at')
            ->paginate(25);

        $totalPending = CommissionEntry::query()
            ->where('beneficiary_promoter_profile_id', $profileId)
            ->where('status', CommissionEntryStatus::Pending)
            ->sum('amount');

        return view('promotor.komisi.pending', compact('commissions', 'totalPending'));
    }

    public function payable(Request $request): View
    {
        $profileId = $this->getCurrentProfileId();

        $commissions = CommissionEntry::query()
            ->where('beneficiary_promoter_profile_id', $profileId)
            ->where('status', CommissionEntryStatus::Payable)
            ->with(['order', 'orderItem', 'commissionRule'])
            ->latest('earned_at')
            ->paginate(25);

        $totalPayable = CommissionEntry::query()
            ->where('beneficiary_promoter_profile_id', $profileId)
            ->where('status', CommissionEntryStatus::Payable)
            ->sum('amount');

        return view('promotor.komisi.payable', compact('commissions', 'totalPayable'));
    }

    public function paid(Request $request): View
    {
        $profileId = $this->getCurrentProfileId();

        $commissions = CommissionEntry::query()
            ->where('beneficiary_promoter_profile_id', $profileId)
            ->where('status', CommissionEntryStatus::Paid)
            ->with(['order', 'orderItem', 'payout'])
            ->latest('paid_at')
            ->paginate(25);

        $totalPaid = CommissionEntry::query()
            ->where('beneficiary_promoter_profile_id', $profileId)
            ->where('status', CommissionEntryStatus::Paid)
            ->sum('amount');

        return view('promotor.komisi.paid', compact('commissions', 'totalPaid'));
    }

    public function reversed(Request $request): View
    {
        $profileId = $this->getCurrentProfileId();

        $commissions = CommissionEntry::query()
            ->where('beneficiary_promoter_profile_id', $profileId)
            ->where('status', CommissionEntryStatus::Reversed)
            ->with(['order', 'orderItem', 'reversalEntries'])
            ->latest('reversed_at')
            ->paginate(25);

        $totalReversed = CommissionEntry::query()
            ->where('beneficiary_promoter_profile_id', $profileId)
            ->where('status', CommissionEntryStatus::Reversed)
            ->sum('amount');

        return view('promotor.komisi.pending', compact('commissions', 'totalReversed'));
    }

    private function getCurrentProfileId(): ?int
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return $user?->promoterProfile?->id;
    }
}
