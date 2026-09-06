<div class="space-y-4">
    <div class="border border-gray-200 dark:border-gray-600 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <div class="font-bold text-gray-900 dark:text-white">{{ $targetUser->name }}</div>
                <div class="text-xs text-gray-500">User ID: {{ $targetUser->id }} · Role: {{ $targetUser->role?->value ?? $targetUser->role }}</div>
            </div>
            <button wire:click="save"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow disabled:opacity-50">
                <span wire:loading.remove>💾 Simpan Permissions</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>

        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
            @foreach($permissions as $group => $perms)
                <div>
                    <div class="text-xs font-bold uppercase text-gray-500 tracking-wider mb-2 pb-1 border-b border-gray-100 dark:border-gray-700">
                        {{ $group }}
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($perms as $p)
                            <label class="inline-flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer">
                                <input type="checkbox"
                                       wire:model="checkedPermissions"
                                       value="{{ (string)$p->id }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs">
                                    <span class="font-mono text-gray-900 dark:text-white">{{ $p->key }}</span>
                                    @if($p->description)
                                        <div class="text-[10px] text-gray-500">{{ $p->description }}</div>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if(session('status'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-3 text-green-700 dark:text-green-300 text-sm">
            {{ session('status') }}
        </div>
    @endif
</div>
