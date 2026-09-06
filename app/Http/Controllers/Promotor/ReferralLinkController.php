<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\ReferralLink;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralLinkController extends Controller
{
    public function index(): View
    {
        $profileId = auth()->user()->promoterProfile?->id;

        $links = ReferralLink::query()
            ->where('promoter_profile_id', $profileId)
            ->withCount('visits')
            ->latest()
            ->paginate(20);

        return view('promotor.referral-links.index', compact('links'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profileId = auth()->user()->promoterProfile?->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination_path' => 'nullable|string|max:500',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'expires_at' => 'nullable|date',
        ]);

        ReferralLink::query()->create([
            ...$validated,
            'promoter_profile_id' => $profileId,
            'slug' => Str::random(12),
            'is_active' => true,
        ]);

        return back()->with('status', 'Referral link dibuat.');
    }
}
