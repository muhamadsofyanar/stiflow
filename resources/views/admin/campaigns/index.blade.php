<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Campaign</h2><a href="{{ route('admin.campaigns.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Tambah Campaign</a></div></x-slot>
    <div class="py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-sm"><thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-6 py-3 text-left">Nama</th><th class="px-6 py-3 text-left">Channel</th><th class="px-6 py-3 text-left">Status</th><th class="px-6 py-3 text-left">Terkirim</th><th class="px-6 py-3"></th></tr></thead><tbody class="divide-y dark:divide-gray-700">
        @forelse($campaigns as $campaign)
            <tr><td class="px-6 py-4 font-semibold">{{ $campaign->name }}</td><td class="px-6 py-4">{{ $campaign->channel?->value ?? $campaign->channel }}</td><td class="px-6 py-4">{{ $campaign->status?->value ?? $campaign->status }}</td><td class="px-6 py-4">{{ $campaign->sent_count }}/{{ $campaign->total_recipients }}</td><td class="px-6 py-4 text-right"><a class="text-blue-600" href="{{ route('admin.campaigns.show', $campaign) }}">Detail</a> · <a class="text-amber-600" href="{{ route('admin.campaigns.edit', $campaign) }}">Edit</a></td></tr>
        @empty<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada campaign.</td></tr>@endforelse
        </tbody></table><div class="px-6 py-4">{{ $campaigns->links() }}</div>
    </div></div></div>
</x-app-layout>
