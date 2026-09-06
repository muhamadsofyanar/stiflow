<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            Pipeline::query()->update(['is_default' => false]);
        }

        Pipeline::query()->create([...$validated, 'owner_user_id' => $request->user()->id]);

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            Pipeline::query()->whereKeyNot($pipeline->id)->update(['is_default' => false]);
        }

        $pipeline->update($validated);

        return redirect()->route('admin.pipelines.index')->with('status', 'Pipeline diperbarui.');
    }

    public function destroy(Pipeline $pipeline): RedirectResponse
    {
        $pipeline->delete();

        return redirect()->route('admin.pipelines.index')->with('status', 'Pipeline dihapus.');
    }

    public function storeStage(Request $request, Pipeline $pipeline): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0',
            'color_hex' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_won_stage' => 'boolean',
            'is_lost_stage' => 'boolean',
        ]);

        $lastPosition = $pipeline->stages()->max('position');

        $pipeline->stages()->create([
            ...$validated,
            'slug' => $this->uniqueStageSlug($pipeline, $validated['name']),
            'position' => $validated['position'] ?? ($lastPosition === null ? 0 : (int) $lastPosition + 1),
        ]);

        return back()->with('status', 'Stage ditambahkan.');
    }

    public function updateStage(Request $request, Pipeline $pipeline, PipelineStage $stage): RedirectResponse
    {
        abort_unless($stage->pipeline_id === $pipeline->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0',
            'color_hex' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_won_stage' => 'boolean',
            'is_lost_stage' => 'boolean',
        ]);

        $stage->update([
            ...$validated,
            'slug' => $this->uniqueStageSlug($pipeline, $validated['name'], $stage),
        ]);

        return back()->with('status', 'Stage diperbarui.');
    }

    public function destroyStage(Pipeline $pipeline, PipelineStage $stage): RedirectResponse
    {
        abort_unless($stage->pipeline_id === $pipeline->id, 404);
        $stage->delete();

        return back()->with('status', 'Stage dihapus.');
    }

    private function uniqueStageSlug(Pipeline $pipeline, string $name, ?PipelineStage $except = null): string
    {
        $base = Str::slug($name) ?: 'stage';
        $slug = $base;
        $suffix = 2;

        while ($pipeline->stages()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except->id))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
