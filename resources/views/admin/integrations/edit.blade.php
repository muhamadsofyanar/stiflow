<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Integrasi: {{ $integration->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Kembali</a>
                <form method="POST" action="{{ route('admin.integrations.destroy', $integration) }}" onsubmit="return confirm('Hapus integrasi ini?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">Hapus</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                @livewire(\App\Livewire\IntegrationTestConnectionButton::class, ['integration' => $integration])
            </div>

            <form method="POST" action="{{ route('admin.integrations.update', $integration) }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 space-y-6">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="name" value="Nama Koneksi *" />
                        <x-text-input id="name" name="name" class="mt-1 w-full" required :value="old('name', $integration->name)" />
                    </div>
                    <div>
                        <x-input-label for="provider" value="Provider *" />
                        <x-text-input id="provider" name="provider" class="mt-1 w-full" required :value="old('provider', $integration->provider)" />
                    </div>
                    <div>
                        <x-input-label for="category" value="Kategori *" />
                        <select id="category" name="category" class="mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-lg">
                            @foreach(['email','sms','whatsapp','payment','crm','storage','other'] as $c)
                                <option value="{{ $c }}" @selected(old('category', $integration->category) === $c)>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-lg">
                            @foreach(['pending','connected','disconnected','failed'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $integration->status?->value ?? $integration->status) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="config_json" value="Config (JSON)" />
                        <textarea id="config_json" name="config_json" rows="8" class="mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-lg font-mono text-sm">{{ old('config_json', is_array($integration->config_json) ? json_encode($integration->config_json, JSON_PRETTY_PRINT) : $integration->config_json) }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 text-sm border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Batal</a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
