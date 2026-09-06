<?php

namespace App\Livewire;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PermissionMatrixEdit extends Component
{
    public int $userId;

    public array $checkedPermissions = [];

    public function mount(int $userId): void
    {
        $this->userId = $userId;
        $user = User::query()->findOrFail($userId);
        $this->checkedPermissions = $user->permissions()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function save(): void
    {
        $user = User::query()->findOrFail($this->userId);
        abort_if($user->isAdmin(), 403, 'Admin tidak dapat diubah permissions.');
        abort_if(! auth()->user()?->hasPermission('users.manage') && ! auth()->user()?->isAdmin(), 403);

        DB::transaction(function () use ($user) {
            $ids = array_map('intval', $this->checkedPermissions);
            $user->permissions()->sync($ids);
        });

        session()->flash('status', 'Permissions berhasil disimpan.');
    }

    public function render()
    {
        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        $targetUser = User::query()->findOrFail($this->userId);

        return view('livewire.permission-matrix-edit', compact('permissions', 'targetUser'));
    }
}
