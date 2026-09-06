<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index(): View
    {
        $pipelines = Pipeline::query()->withCount('stages')->latest()->paginate(15);

        return view('admin.pipelines.index', compact('pipelines'));
    }

    public function create(): View
    {
        return view('admin.pipelines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Pipeline::query()->create($validated);

        return redirect()->route('admin.pipelines.index')->with('status', 'Pipeline dibuat.');
    }

    public function show(Pipeline $pipeline): View
    {
        $pipeline->load(['stages.contacts' => fn ($q) => $q->with('ownerPromoterProfile')]);

        return view('admin.pipelines.board', compact('pipeline'));
    }

    public function edit(Pipeline $pipeline): View
    {
        return view('admin.pipelines.edit', compact('pipeline'));
    }

    public function update(Request $request, Pipeline $pipeline): RedirectResponse
    {
        $pipeline->update($request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]));

        return redirect()->route('admin.pipelines.index')->with('status', 'Pipeline diperbarui.');
    }

    public function destroy(Pipeline $pipeline): RedirectResponse
    {
        $pipeline->delete();

        return redirect()->route('admin.pipelines.index')->with('status', 'Pipeline dihapus.');
    }

    public function storeStage(Request $request, Pipeline $pipeline): RedirectResponse
    {
        $pipeline->stages()->create($request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0',
            'win_probability' => 'nullable|integer|min:0|max:100',
        ]));

        return back()->with('status', 'Stage ditambahkan.');
    }

    public function updateStage(Request $request, Pipeline $pipeline, PipelineStage $stage): RedirectResponse
    {
        $stage->update($request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0',
            'win_probability' => 'nullable|integer|min:0|max:100',
        ]));

        return back()->with('status', 'Stage diperbarui.');
    }

    public function destroyStage(Pipeline $pipeline, PipelineStage $stage): RedirectResponse
    {
        $stage->delete();

        return back()->with('status', 'Stage dihapus.');
    }
}
