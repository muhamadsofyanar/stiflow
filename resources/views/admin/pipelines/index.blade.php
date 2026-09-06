<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Pipeline CRM</h2><a href="{{ route('admin.pipelines.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Tambah Pipeline</a></div></x-slot>
    <div class="py-10"><div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @forelse($pipelines as $pipeline)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 flex justify-between"><div><div class="font-bold">{{ $pipeline->name }}</div><div class="text-sm text-gray-500">{{ $pipeline->description ?: 'Tanpa deskripsi' }} · {{ $pipeline->stages_count }} stage</div></div><div class="space-x-3"><a class="text-blue-600" href="{{ route('admin.pipelines.show', $pipeline) }}">Board</a><a class="text-amber-600" href="{{ route('admin.pipelines.edit', $pipeline) }}">Edit</a></div></div>
        @empty<div class="bg-white dark:bg-gray-800 rounded-2xl p-12 text-center text-gray-500">Belum ada pipeline.</div>@endforelse
        {{ $pipelines->links() }}
    </div></div>
</x-app-layout>
