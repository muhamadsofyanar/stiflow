<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        MessageTemplate::query()->create($request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|string',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'is_system' => 'boolean',
        ]));

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
        $template->update($request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|string',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'is_system' => 'boolean',
        ]));

        return redirect()->route('admin.templates.index')->with('status', 'Template diperbarui.');
    }

    public function destroy(MessageTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('admin.templates.index')->with('status', 'Template dihapus.');
    }
}
