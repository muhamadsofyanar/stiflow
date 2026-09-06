<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationLicense;
use App\Services\Licensing\ApplicationLicenseValidatorService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationLicenseController extends Controller
{
    public function show(ApplicationLicenseValidatorService $service): View
    {
        $installationUuid = $service->getOrGenerateInstallationUuid();
        $license = ApplicationLicense::query()
            ->where('installation_uuid', $installationUuid)
            ->first();

        return view('admin.license.show', compact('license', 'installationUuid'));
    }

    public function activate(Request $request, ApplicationLicenseValidatorService $service): RedirectResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string|min:10',
        ]);

        $result = $service->activateLocal($validated['license_key']);

        if (! $result['success']) {
            return redirect()->route('admin.license.show')
                ->withErrors(['license_key' => $result['message'] ?? 'Aktivasi gagal.']);
        }

        return redirect()->route('admin.license.show')->with('status', 'Lisensi berhasil diaktifkan.');
    }
}
