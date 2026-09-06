<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Pipeline;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function index(Request $request): View
    {
        $profileId = auth()->user()->promoterProfile?->id;

        $contacts = Contact::query()
            ->where(function ($q) use ($profileId) {
                $q->where('owner_promoter_profile_id', $profileId)
                    ->orWhere('referred_by_promoter_profile_id', $profileId);
            })
            ->with(['stage', 'pipeline'])
            ->latest()
            ->paginate(20);

        return view('promotor.crm.index', compact('contacts'));
    }

    public function show(Contact $contact): View
    {
        $profileId = auth()->user()->promoterProfile?->id;
        abort_unless(
            $contact->owner_promoter_profile_id === $profileId || $contact->referred_by_promoter_profile_id === $profileId,
            403,
            'Akses ditolak: kontak bukan milik Anda.'
        );

        $contact->load(['activities', 'tags', 'stifinResults']);

        return view('promotor.crm.show', compact('contact'));
    }

    public function board(Request $request, ?Pipeline $pipeline = null): View
    {
        $profileId = auth()->user()->promoterProfile?->id;

        if (! $pipeline) {
            $pipeline = Pipeline::query()->where('is_active', true)->first();
        }

        $pipelines = Pipeline::query()->where('is_active', true)->get();

        $stages = $pipeline
            ? $pipeline->stages()->with(['contacts' => function ($q) use ($profileId) {
                $q->where(function ($sub) use ($profileId) {
                    $sub->where('owner_promoter_profile_id', $profileId)
                        ->orWhere('referred_by_promoter_profile_id', $profileId);
                })->with('ownerPromoterProfile');
            }])->orderBy('position')->get()
            : collect();

        return view('promotor.crm.board', compact('pipeline', 'pipelines', 'stages'));
    }
}
