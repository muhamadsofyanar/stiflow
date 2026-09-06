<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Hasil STIFIN</h2><a href="{{ route('admin.stifin-results.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Tambah Hasil</a></div></x-slot>
    <div class="py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-sm"><thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-6 py-3 text-left">Nama</th><th class="px-6 py-3 text-left">Kontak/Member</th><th class="px-6 py-3 text-left">Tipe</th><th class="px-6 py-3 text-left">Tanggal Tes</th><th class="px-6 py-3"></th></tr></thead><tbody class="divide-y dark:divide-gray-700">
        @forelse($results as $result)
            <tr><td class="px-6 py-4 font-semibold">{{ $result->name ?? '-' }}</td><td class="px-6 py-4">{{ $result->contact?->full_name ?? $result->memberUser?->name ?? '-' }}</td><td class="px-6 py-4">{{ $result->result_type }}</td><td class="px-6 py-4">{{ $result->test_taken_date?->format('d-m-Y') ?? '-' }}</td><td class="px-6 py-4 text-right"><a class="text-blue-600" href="{{ route('admin.stifin-results.show', $result) }}">Detail</a> · <a class="text-amber-600" href="{{ route('admin.stifin-results.edit', $result) }}">Edit</a></td></tr>
        @empty<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada hasil STIFIN.</td></tr>@endforelse
        </tbody></table><div class="px-6 py-4">{{ $results->links() }}</div>
    </div></div></div>
</x-app-layout>
