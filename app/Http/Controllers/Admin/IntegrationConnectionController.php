<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationConnection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IntegrationConnectionController extends Controller
{
    public function index(): View
    {
        $integrations = IntegrationConnection::query()->latest()->paginate(20);

        return view('admin.integrations.index', compact('integrations'));
    }

    public function create(): View
    {
        return view('admin.integrations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        IntegrationConnection::query()->create($request->validate([
            'provider' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'config_json' => 'nullable',
            'status' => 'nullable|string',
        ]));

        return redirect()->route('admin.integrations.index')->with('status', 'Integrasi dibuat.');
    }

    public function edit(IntegrationConnection $integration): View
    {
        return view('admin.integrations.edit', compact('integration'));
    }

    public function update(Request $request, IntegrationConnection $integration): RedirectResponse
    {
        $integration->update($request->validate([
            'provider' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'config_json' => 'nullable',
            'status' => 'nullable|string',
        ]));

        return redirect()->route('admin.integrations.index')->with('status', 'Integrasi diperbarui.');
    }

    public function destroy(IntegrationConnection $integration): RedirectResponse
    {
        $integration->delete();

        return redirect()->route('admin.integrations.index')->with('status', 'Integrasi dihapus.');
    }
}
