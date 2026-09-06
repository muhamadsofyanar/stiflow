<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Koneksi Integrasi</h2>
            <a href="{{ route('admin.integrations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">+ Tambah Integrasi</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 text-green-700 dark:text-green-300 mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($integrations as $int)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-3">
                            <div class="font-bold text-gray-900 dark:text-white">{{ $int->display_name }}</div>
                            <x-status-badge :value="$int->status?->value ?? $int->status" />
                        </div>
                        <div class="text-sm text-gray-500 mb-2">
                            <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded text-xs font-mono">{{ $int->provider_type }}</span>
                            <span class="ml-2 text-xs">{{ $int->provider_category?->value ?? $int->provider_category }}</span>
                        </div>
                        <div class="text-xs text-gray-400 mb-4">
                            Terakhir diuji: {{ $int->last_tested_at?->format('Y-m-d H:i') ?? 'Belum pernah' }}
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.integrations.edit', $int) }}" class="flex-1 text-center text-sm font-semibold text-amber-600 hover:underline border border-amber-200 rounded-lg py-2">Edit</a>
                            @livewire(\App\Livewire\IntegrationTestConnectionButton::class, ['integration' => $int], key('test-'.$int->id))
                        </div>
                    </div>
                @endforeach
                @if($integrations->isEmpty())
                    <div class="col-span-full bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 p-12 text-center text-gray-500">
                        Belum ada integrasi terdaftar. Klik "Tambah Integrasi" untuk memulai.
                    </div>
                @endif
            </div>

            <div class="mt-6">
                {{ $integrations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
