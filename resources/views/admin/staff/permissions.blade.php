<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Staff Permission Matrix</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @if(session('status'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 text-green-700 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 font-bold text-gray-900 dark:text-white">
                    Daftar Staff & Permission Matrix
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($staff as $s)
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $s->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $s->email }} · {{ $s->role?->value ?? ucfirst($s->role) }}</div>
                                </div>
                                @if(!$s->isAdmin())
                                    @livewire(\App\Livewire\PermissionMatrixEdit::class, ['userId' => $s->id], key('perm-'.$s->id))
                                @else
                                    <span class="px-3 py-1 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded-full text-xs font-bold">ADMIN - Full Access</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if($staff->isEmpty())
                        <div class="p-8 text-center text-gray-500">Belum ada staff terdaftar.</div>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $staff->links() }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Referensi Permissions</h3>
                @foreach($permissions as $group => $perms)
                    <div class="mb-4">
                        <div class="text-xs font-bold uppercase text-gray-500 mb-2 tracking-wider">{{ $group }}</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($perms as $p)
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono">{{ $p->key }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
