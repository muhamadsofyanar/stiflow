<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ContactList;
use App\Models\IntegrationConnection;
use App\Models\MessageTemplate;
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
        return view('admin.campaigns.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:broadcast,transactional,follow_up',
            'channel' => 'required|in:email,whatsapp,sms,telegram,internal',
            'message_template_id' => 'nullable|exists:message_templates,id',
            'sender_integration_connection_id' => 'nullable|exists:integration_connections,id',
            'contact_list_id' => 'nullable|exists:contact_lists,id',
            'segment_id' => 'nullable|exists:segments,id',
            'audience_type' => 'nullable|in:list,segment',
            'schedule_send_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,paused,cancelled',
        ]);

        Campaign::query()->create([...$validated, 'launched_by_user_id' => $request->user()->id]);

        return redirect()->route('admin.campaigns.index')->with('status', 'Kampanye dibuat.');
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load(['recipients', 'messageTemplate', 'contactList', 'segment', 'senderIntegrationConnection']);

        return view('admin.campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign): View
    {
        return view('admin.campaigns.edit', ['campaign' => $campaign, ...$this->formOptions()]);
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $campaign->update($request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:broadcast,transactional,follow_up',
            'channel' => 'required|in:email,whatsapp,sms,telegram,internal',
            'message_template_id' => 'nullable|exists:message_templates,id',
            'sender_integration_connection_id' => 'nullable|exists:integration_connections,id',
            'contact_list_id' => 'nullable|exists:contact_lists,id',
            'segment_id' => 'nullable|exists:segments,id',
            'audience_type' => 'nullable|in:list,segment',
            'schedule_send_at' => 'nullable|date',
            'status' => 'required|in:draft,scheduled,paused,cancelled',
        ]));

        return redirect()->route('admin.campaigns.index')->with('status', 'Kampanye diperbarui.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()->route('admin.campaigns.index')->with('status', 'Kampanye dihapus.');
    }

    public function listIndex(): View
    {
        $lists = ContactList::query()->withCount('members')->latest()->paginate(20);

        return view('admin.campaigns.lists', compact('lists'));
    }

    public function storeList(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_marketable_only' => 'boolean',
        ]);

        ContactList::query()->create([
            ...$validated,
            'is_marketable_only' => $request->boolean('is_marketable_only'),
            'created_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'List dibuat.');
    }

    public function updateList(Request $request, ContactList $list): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_marketable_only' => 'boolean',
        ]);

        $list->update([...$validated, 'is_marketable_only' => $request->boolean('is_marketable_only')]);

        return back()->with('status', 'List diperbarui.');
    }

    public function destroyList(ContactList $list): RedirectResponse
    {
        $list->delete();

        return back()->with('status', 'List dihapus.');
    }

    public function segmentIndex(): View
    {
        $segments = Segment::query()->latest()->paginate(20);

        return view('admin.campaigns.segments', compact('segments'));
    }

    public function storeSegment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'filter_rules_json' => 'required|json',
        ]);

        Segment::query()->create([
            ...$validated,
            'filter_rules_json' => json_decode($validated['filter_rules_json'], true, 512, JSON_THROW_ON_ERROR),
            'created_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Segment dibuat.');
    }

    public function updateSegment(Request $request, Segment $segment): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'filter_rules_json' => 'required|json',
        ]);

        $segment->update([
            ...$validated,
            'filter_rules_json' => json_decode($validated['filter_rules_json'], true, 512, JSON_THROW_ON_ERROR),
        ]);

        return back()->with('status', 'Segment diperbarui.');
    }

    public function destroySegment(Segment $segment): RedirectResponse
    {
        $segment->delete();

        return back()->with('status', 'Segment dihapus.');
    }

    private function formOptions(): array
    {
        return [
            'templates' => MessageTemplate::query()->where('is_active', true)->orderBy('name')->get(),
            'lists' => ContactList::query()->orderBy('name')->get(),
            'segments' => Segment::query()->orderBy('name')->get(),
            'integrations' => IntegrationConnection::query()->where('is_active', true)->orderBy('display_name')->get(),
        ];
    }
}
