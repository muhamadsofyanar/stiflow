<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StifinResult;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StifinResultAdminController extends Controller
{
    public function index(): View
    {
        $results = StifinResult::query()->with(['contact', 'order'])->latest()->paginate(20);

        return view('admin.stifin-results.index', compact('results'));
    }

    public function create(): View
    {
        return view('admin.stifin-results.create');
    }

    public function store(Request $request): RedirectResponse
    {
        StifinResult::query()->create($request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'order_id' => 'nullable|exists:orders,id',
            'stifin_code' => 'required|string|max:50',
            'finest_element' => 'nullable|string|max:100',
            'personal_type' => 'nullable|string|max:100',
            'learning_style' => 'nullable|string|max:100',
            'work_style' => 'nullable|string|max:100',
            'report_data_json' => 'nullable|json',
        ]));

        return redirect()->route('admin.stifin-results.index')->with('status', 'Hasil STIFIN dibuat.');
    }

    public function show(StifinResult $stifinResult): View
    {
        $stifinResult->load(['contact', 'order']);

        return view('admin.stifin-results.show', compact('stifinResult'));
    }

    public function edit(StifinResult $stifinResult): View
    {
        return view('admin.stifin-results.edit', compact('stifinResult'));
    }

    public function update(Request $request, StifinResult $stifinResult): RedirectResponse
    {
        $stifinResult->update($request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'order_id' => 'nullable|exists:orders,id',
            'stifin_code' => 'required|string|max:50',
            'finest_element' => 'nullable|string|max:100',
            'personal_type' => 'nullable|string|max:100',
            'learning_style' => 'nullable|string|max:100',
            'work_style' => 'nullable|string|max:100',
            'report_data_json' => 'nullable',
        ]));

        return redirect()->route('admin.stifin-results.index')->with('status', 'Hasil STIFIN diperbarui.');
    }

    public function destroy(StifinResult $stifinResult): RedirectResponse
    {
        $stifinResult->delete();

        return redirect()->route('admin.stifin-results.index')->with('status', 'Hasil STIFIN dihapus.');
    }
}
