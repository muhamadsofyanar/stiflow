<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Pipeline Kanban - {{ $pipeline?->name ?? 'Default' }}
            </h2>
            <a href="{{ route('admin.pipelines.edit', $pipeline) }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm">Edit Pipeline</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <form method="POST" action="{{ route('admin.pipelines.stages.store', $pipeline) }}" class="bg-white dark:bg-gray-800 rounded-2xl p-5 flex gap-3">@csrf
                <input name="name" required placeholder="Nama stage" class="rounded-lg border-gray-300 dark:bg-gray-800">
                <input name="color_hex" value="#64748b" pattern="#[0-9A-Fa-f]{6}" class="rounded-lg border-gray-300 dark:bg-gray-800">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Tambah Stage</button>
            </form>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                @forelse($pipeline->stages->sortBy('position') as $stage)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border-t-4" style="border-color: {{ $stage->color_hex ?? '#64748b' }}"><div class="font-bold mb-4">{{ $stage->name }}</div>@forelse($stage->contacts as $contact)<div class="border rounded-lg p-3 mb-2"><div class="font-semibold">{{ $contact->full_name }}</div><div class="text-xs text-gray-500">{{ $contact->phone ?? $contact->email }}</div></div>@empty<p class="text-sm text-gray-500">Belum ada kontak.</p>@endforelse</div>
                @empty<div class="col-span-full bg-white dark:bg-gray-800 rounded-2xl p-10 text-center text-gray-500">Tambahkan stage pertama.</div>@endforelse
            </div>
        </div>
    </div>
</x-app-layout>
