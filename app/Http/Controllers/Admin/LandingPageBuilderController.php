<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Enums\LandingPageStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LandingPageBuilderController extends Controller
{
    public function index(): View
    {
        $landingPages = LandingPage::query()
            ->withCount('visits')
            ->latest()
            ->paginate(20);

        return view('admin.landing-pages.index', compact('landingPages'));
    }

    public function create(): View
    {
        $landingPage = new LandingPage([
            'status' => LandingPageStatus::Draft,
            'blocks_json' => [],
            'meta_json' => [],
        ]);

        return view('admin.landing-pages.edit', compact('landingPage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:landing_pages,slug',
            'status' => 'required|in:Draft,Published',
            'blocks_json' => 'nullable|string',
            'meta_json' => 'nullable|string',
        ]);

        $blocks = json_decode($validated['blocks_json'] ?? '[]', true) ?: [];
        $meta = json_decode($validated['meta_json'] ?? '[]', true) ?: [];

        $landingPage = LandingPage::query()->create([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Str::slug($validated['title']).'-'.Str::random(6),
            'status' => $validated['status'],
            'blocks_json' => $blocks,
            'meta_json' => $meta,
            'published_at' => $validated['status'] === LandingPageStatus::Published->value ? now() : null,
            'created_by_user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.landing-pages.edit', $landingPage)->with('status', 'Landing Page dibuat.');
    }

    public function edit(LandingPage $landingPage): View
    {
        return view('admin.landing-pages.edit', compact('landingPage'));
    }

    public function update(Request $request, LandingPage $landingPage): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:landing_pages,slug,'.$landingPage->id,
            'status' => 'required|in:Draft,Published',
            'blocks_json' => 'nullable|string',
            'meta_json' => 'nullable|string',
        ]);

        $blocks = json_decode($validated['blocks_json'] ?? '[]', true) ?: [];
        $meta = json_decode($validated['meta_json'] ?? '[]', true) ?: [];

        $oldStatus = $landingPage->status;

        $landingPage->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'status' => $validated['status'],
            'blocks_json' => $blocks,
            'meta_json' => $meta,
            'published_at' => $oldStatus !== LandingPageStatus::Published && $validated['status'] === LandingPageStatus::Published->value
                ? now()
                : $landingPage->published_at,
        ]);

        return redirect()->route('admin.landing-pages.edit', $landingPage)->with('status', 'Landing Page diperbarui.');
    }
}
