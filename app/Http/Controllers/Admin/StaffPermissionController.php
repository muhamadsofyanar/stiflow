<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffPermissionController extends Controller
{
    public function index(): View
    {
        $staff = User::query()
            ->whereIn('role', [UserRole::Staff, UserRole::Admin])
            ->with('permissions')
            ->latest()
            ->paginate(20);

        $permissions = Permission::query()->orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.staff.permissions', compact('staff', 'permissions'));
    }

    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        abort_if($user->isAdmin(), 400, 'Admin tidak dapat diubah permissions via matrix.');

        DB::transaction(function () use ($user, $validated) {
            $user->permissions()->sync($validated['permissions'] ?? []);
        });

        return back()->with('status', 'Permissions staff diperbarui.');
    }
}
