<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\BranchSetting;
use App\Services\Audit\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchSettingController extends Controller
{
    public function edit(): View
    {
        $setting = BranchSetting::current();

        return view('admin.branch-settings.edit', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_code' => 'required|string|max:32',
            'brand_name' => 'required|string|max:120',
            'contact' => 'nullable|string|max:40',
            'address' => 'nullable|string|max:500',
            'bank_name' => 'required|string|max:40',
            'bank_account' => 'required|string|max:40',
            'bank_account_name' => 'required|string|max:120',
            'locale' => 'required|string|max:8',
            'timezone' => 'required|string|max:64',
            'currency' => 'required|string|max:8',
        ]);

        $setting = BranchSetting::current();
        $before = $setting->toArray();
        $setting->fill($validated);
        $setting->save();

        AuditService::record(
            action: AuditAction::BranchSettingsUpdated,
            subject: $setting,
            before: $before,
            after: $setting->toArray(),
        );

        return back()->with('status', 'Pengaturan cabang disimpan.');
    }
}
