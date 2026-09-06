<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $contacts = Contact::query()
            ->with(['ownerPromoterProfile', 'stage', 'pipeline'])
            ->latest()
            ->paginate(20);

        return view('admin.contacts.index', compact('contacts'));
    }

    public function create(): View
    {
        return view('admin.contacts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|string',
            'owner_promoter_profile_id' => 'nullable|exists:promoter_profiles,id',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'stage_id' => 'nullable|exists:pipeline_stages,id',
        ]);

        Contact::query()->create($validated);

        return redirect()->route('admin.contacts.index')->with('status', 'Kontak berhasil dibuat.');
    }

    public function show(Contact $contact): View
    {
        $contact->load(['activities', 'tags', 'customValues', 'stifinResults']);

        return view('admin.contacts.show', compact('contact'));
    }

    public function edit(Contact $contact): View
    {
        return view('admin.contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|string',
            'owner_promoter_profile_id' => 'nullable|exists:promoter_profiles,id',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'stage_id' => 'nullable|exists:pipeline_stages,id',
        ]);

        $contact->update($validated);

        return redirect()->route('admin.contacts.show', $contact)->with('status', 'Kontak berhasil diperbarui.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')->with('status', 'Kontak berhasil dihapus.');
    }
}
