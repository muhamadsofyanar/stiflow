<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\MessageChannel;
use App\Models\MessageTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageTemplateAdminController extends Controller
{
    public function index(): View
    {
        $templates = MessageTemplate::query()->latest()->paginate(20);

        return view('admin.campaigns.templates', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.campaigns.template-create');
    }

    public function store(Request $request): RedirectResponse
    {
        MessageTemplate::query()->create([
            ...$this->validated($request),
            'created_by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.templates.index')->with('status', 'Template dibuat.');
    }

    public function show(MessageTemplate $template): View
    {
        return view('admin.campaigns.template-show', compact('template'));
    }

    public function edit(MessageTemplate $template): View
    {
        return view('admin.campaigns.template-edit', compact('template'));
    }

    public function update(Request $request, MessageTemplate $template): RedirectResponse
    {
        $template->update($this->validated($request));

        return redirect()->route('admin.templates.index')->with('status', 'Template diperbarui.');
    }

    public function destroy(MessageTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('admin.templates.index')->with('status', 'Template dihapus.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:'.implode(',', array_column(MessageChannel::cases(), 'value')),
            'template_type' => 'required|string|max:100',
            'subject_line' => 'nullable|string|max:255',
            'content_body' => 'required|string',
            'language_code' => 'required|string|max:10',
            'placeholders_json' => 'nullable|json',
            'has_approved_external_template' => 'boolean',
            'external_template_provider_id' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['placeholders_json'] = filled($validated['placeholders_json'] ?? null)
            ? json_decode($validated['placeholders_json'], true, 512, JSON_THROW_ON_ERROR)
            : null;
        $validated['has_approved_external_template'] = $request->boolean('has_approved_external_template');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
