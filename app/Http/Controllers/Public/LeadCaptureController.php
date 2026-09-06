<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContactStatus;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ReferralLink;
use App\Models\ReferralVisit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadCaptureController extends Controller
{
    public function showForm(Request $request): View
    {
        $slug = $request->cookie('stiflow_referral_slug') ?? $request->query('ref');

        $referralLink = $slug ? ReferralLink::query()->where('slug', $slug)->first() : null;

        return view('public.lead-capture.form', compact('referralLink'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'referral_slug' => 'nullable|string|max:100',
            'source_channel' => 'nullable|string|max:100',
        ]);

        $slug = $validated['referral_slug'] ?? $request->cookie('stiflow_referral_slug');
        $referralLink = $slug ? ReferralLink::query()->where('slug', $slug)->first() : null;

        DB::transaction(function () use ($validated, $referralLink) {
            $contact = Contact::query()->create([
                'full_name' => $validated['full_name'],
                'first_name' => explode(' ', trim($validated['full_name']))[0] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'whatsapp' => $validated['whatsapp'] ?? null,
                'status' => ContactStatus::Lead,
                'source_channel' => $validated['source_channel'] ?? 'referral',
                'owner_promoter_profile_id' => $referralLink?->promoter_profile_id,
                'referred_by_promoter_profile_id' => $referralLink?->promoter_profile_id,
            ]);

            if ($referralLink) {
                ReferralVisit::query()
                    ->where('referral_link_id', $referralLink->id)
                    ->whereNull('converted_to_contact_id')
                    ->latest()
                    ->first()
                    ?->update(['converted_to_contact_id' => $contact->id]);

                $referralLink->increment('total_leads');
            }
        });

        return back()->with('status', 'Terima kasih! Data Anda telah diterima.');
    }
}
