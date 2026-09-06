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
        $validated = $this->validatedData($request, true);

        IntegrationConnection::query()->create([
            ...$validated,
            'encrypted_credentials' => json_decode($validated['credentials_json'], true, 512, JSON_THROW_ON_ERROR),
            'config_json' => $this->decodeOptionalJson($validated['config_json'] ?? null),
            'owned_by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.integrations.index')->with('status', 'Integrasi dibuat.');
    }

    public function edit(IntegrationConnection $integration): View
    {
        return view('admin.integrations.edit', compact('integration'));
    }

    public function update(Request $request, IntegrationConnection $integration): RedirectResponse
    {
        $validated = $this->validatedData($request, false);
        $credentials = $validated['credentials_json'] ?? null;
        unset($validated['credentials_json']);

        $integration->update([
            ...$validated,
            'config_json' => $this->decodeOptionalJson($validated['config_json'] ?? null),
            ...filled($credentials) ? ['encrypted_credentials' => json_decode($credentials, true, 512, JSON_THROW_ON_ERROR)] : [],
        ]);

        return redirect()->route('admin.integrations.index')->with('status', 'Integrasi diperbarui.');
    }

    public function destroy(IntegrationConnection $integration): RedirectResponse
    {
        $integration->delete();

        return redirect()->route('admin.integrations.index')->with('status', 'Integrasi dihapus.');
    }

    private function validatedData(Request $request, bool $credentialsRequired): array
    {
        return $request->validate([
            'display_name' => 'required|string|max:255',
            'provider_name' => 'required|string|max:255',
            'provider_category' => 'required|in:whatsapp,email,sms,stifin_api,payment,license,storage,webpush',
            'provider_type' => 'required|string|max:100',
            'credentials_json' => ($credentialsRequired ? 'required' : 'nullable').'|json',
            'config_json' => 'nullable|json',
            'status' => 'required|in:configured,healthy,degraded,offline,disabled,error',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
        ]);
    }

    private function decodeOptionalJson(?string $value): ?array
    {
        return filled($value) ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : null;
    }
}
