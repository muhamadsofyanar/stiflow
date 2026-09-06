<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ContactList;
use App\Models\Segment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampaignAdminController extends Controller
{
    public function index(): View
    {
        $campaigns = Campaign::query()->latest()->paginate(20);

        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('admin.campaigns.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Campaign::query()->create($request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|string',
            'template_id' => 'nullable|exists:message_templates,id',
            'contact_list_id' => 'nullable|exists:contact_lists,id',
            'segment_id' => 'nullable|exists:segments,id',
            'scheduled_at' => 'nullable|date',
            'status' => 'nullable|string',
        ]));

        return redirect()->route('admin.campaigns.index')->with('status', 'Kampanye dibuat.');
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load(['recipients', 'template', 'list', 'segment']);

        return view('admin.campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign): View
    {
        return view('admin.campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $campaign->update($request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|string',
            'template_id' => 'nullable|exists:message_templates,id',
            'contact_list_id' => 'nullable|exists:contact_lists,id',
            'segment_id' => 'nullable|exists:segments,id',
            'scheduled_at' => 'nullable|date',
            'status' => 'nullable|string',
        ]));

        return redirect()->route('admin.campaigns.index')->with('status', 'Kampanye diperbarui.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()->route('admin.campaigns.index')->with('status', 'Kampanye dihapus.');
    }

    public function storeList(Request $request): RedirectResponse
    {
        ContactList::query()->create($request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]));

        return back()->with('status', 'List dibuat.');
    }

    public function updateList(Request $request, ContactList $list): RedirectResponse
    {
        $list->update($request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]));

        return back()->with('status', 'List diperbarui.');
    }

    public function destroyList(ContactList $list): RedirectResponse
    {
        $list->delete();

        return back()->with('status', 'List dihapus.');
    }

    public function storeSegment(Request $request): RedirectResponse
    {
        Segment::query()->create($request->validate([
            'name' => 'required|string|max:255',
            'filter_rules_json' => 'nullable',
        ]));

        return back()->with('status', 'Segment dibuat.');
    }

    public function updateSegment(Request $request, Segment $segment): RedirectResponse
    {
        $segment->update($request->validate([
            'name' => 'required|string|max:255',
            'filter_rules_json' => 'nullable',
        ]));

        return back()->with('status', 'Segment diperbarui.');
    }

    public function destroySegment(Segment $segment): RedirectResponse
    {
        $segment->delete();

        return back()->with('status', 'Segment dihapus.');
    }
}
