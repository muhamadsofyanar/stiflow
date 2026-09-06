<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StifinResult;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StifinResultAdminController extends Controller
{
    public function index(): View
    {
        $results = StifinResult::query()->with(['contact', 'memberUser'])->latest()->paginate(20);

        return view('admin.stifin-results.index', compact('results'));
    }

    public function create(): View
    {
        $contacts = Contact::query()->orderBy('full_name')->get(['id', 'full_name']);
        $members = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.stifin-results.create', compact('contacts', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        StifinResult::query()->create([
            ...$validated,
            'main_result_json' => $this->decodeJson($validated['main_result_json'] ?? null),
            'uploaded_by_user_id' => $request->user()->id,
            'generated_at' => now(),
        ]);

        return redirect()->route('admin.stifin-results.index')->with('status', 'Hasil STIFIN dibuat.');
    }

    public function show(StifinResult $stifinResult): View
    {
        $stifinResult->load(['contact', 'memberUser', 'uploadedBy']);

        return view('admin.stifin-results.show', compact('stifinResult'));
    }

    public function edit(StifinResult $stifinResult): View
    {
        $contacts = Contact::query()->orderBy('full_name')->get(['id', 'full_name']);
        $members = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.stifin-results.edit', compact('stifinResult', 'contacts', 'members'));
    }

    public function update(Request $request, StifinResult $stifinResult): RedirectResponse
    {
        $validated = $this->validatedData($request, $stifinResult);
        $stifinResult->update([
            ...$validated,
            'main_result_json' => $this->decodeJson($validated['main_result_json'] ?? null),
        ]);

        return redirect()->route('admin.stifin-results.index')->with('status', 'Hasil STIFIN diperbarui.');
    }

    public function destroy(StifinResult $stifinResult): RedirectResponse
    {
        $stifinResult->delete();

        return redirect()->route('admin.stifin-results.index')->with('status', 'Hasil STIFIN dihapus.');
    }

    private function validatedData(Request $request, ?StifinResult $result = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'contact_id' => 'nullable|exists:contacts,id|required_without:member_user_id',
            'member_user_id' => 'nullable|exists:users,id|required_without:contact_id',
            'result_type' => 'required|string|max:100',
            'main_result_json' => 'nullable|json',
            'summary_text' => 'nullable|string',
            'test_taken_date' => 'nullable|date',
            'valid_until_date' => 'nullable|date|after_or_equal:test_taken_date',
            'is_sensitive_locked' => 'boolean',
        ]);
    }

    private function decodeJson(?string $value): ?array
    {
        return filled($value) ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : null;
    }
}
