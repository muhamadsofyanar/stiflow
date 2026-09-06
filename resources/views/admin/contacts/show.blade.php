<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Detail Kontak: {{ $contact->full_name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.contacts.index') }}" class="inline-flex items-center px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Kembali</a>
                <a href="{{ route('admin.contacts.edit', $contact) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600">Edit</a>
                <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" onsubmit="return confirm('Hapus kontak ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Hapus</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Info Dasar</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Nama Lengkap</dt><dd class="font-semibold text-gray-900 dark:text-white">{{ $contact->full_name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="text-gray-900 dark:text-white">{{ $contact->email ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Phone</dt><dd class="text-gray-900 dark:text-white">{{ $contact->phone ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">WhatsApp</dt><dd class="text-gray-900 dark:text-white">{{ $contact->whatsapp ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd><x-status-badge :value="$contact->status?->value ?? $contact->status" /></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Pipeline / Stage</dt><dd class="text-gray-900 dark:text-white">{{ $contact->pipeline?->name ?? '-' }} / {{ $contact->stage?->name ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Nilai Estimasi</dt><dd class="font-semibold">Rp {{ number_format((float)($contact->estimated_value ?? 0), 0, ',', '.') }}</dd></div>
                    </dl>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($contact->tags as $tag)
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 text-xs rounded-full">{{ $tag->name }}</span>
                        @empty
                            <span class="text-gray-500 text-sm">Belum ada tag.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Aktivitas</h3>
                    <div class="space-y-3">
                        @forelse($contact->activities as $act)
                            <div class="border-l-4 border-blue-200 dark:border-blue-800 pl-4 py-2">
                                <div class="text-xs text-gray-500">{{ $act->created_at }}</div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $act->type?->value ?? $act->type }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">{{ $act->summary ?? '-' }}</div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">Belum ada aktivitas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Hasil STIFIN</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-gray-500 text-left">
                                <tr><th class="py-2">Tanggal</th><th>Kode STIFIN</th><th>Tipe Personal</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($contact->stifinResults as $sr)
                                    <tr>
                                        <td class="py-2">{{ $sr->created_at?->format('Y-m-d') }}</td>
                                        <td class="font-mono font-bold">{{ $sr->stifin_code }}</td>
                                        <td>{{ $sr->personal_type ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-gray-500">Belum ada hasil STIFIN.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
