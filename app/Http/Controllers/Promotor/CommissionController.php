<?php

namespace App\Http\Controllers\Promotor;

use App\Enums\CommissionEntryStatus;
use App\Http\Controllers\Controller;
use App\Models\CommissionEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request): View
    {
        $profileId = auth()->user()->promoterProfile?->id;
        $activeTab = $request->input('tab', CommissionEntryStatus::Pending->value);

        $tabs = [
            CommissionEntryStatus::Pending->value => 'Pending',
            CommissionEntryStatus::Payable->value => 'Payable',
            CommissionEntryStatus::Paid->value => 'Paid',
            CommissionEntryStatus::Reversed->value => 'Reversed',
        ];

        $query = CommissionEntry::query()
            ->where('promoter_profile_id', $profileId)
            ->with(['order', 'payout'])
            ->latest();

        if ($activeTab && isset($tabs[$activeTab])) {
            $query->where('status', $activeTab);
        }

        $commissions = $query->paginate(20);

        $totals = [
            'pending' => CommissionEntry::query()->where('promoter_profile_id', $profileId)->where('status', CommissionEntryStatus::Pending)->sum('amount'),
            'payable' => CommissionEntry::query()->where('promoter_profile_id', $profileId)->where('status', CommissionEntryStatus::Payable)->sum('amount'),
            'paid' => CommissionEntry::query()->where('promoter_profile_id', $profileId)->where('status', CommissionEntryStatus::Paid)->sum('amount'),
            'reversed' => CommissionEntry::query()->where('promoter_profile_id', $profileId)->where('status', CommissionEntryStatus::Reversed)->sum('amount'),
        ];

        return view('promotor.komisi.index', compact('commissions', 'tabs', 'activeTab', 'totals'));
    }
}
