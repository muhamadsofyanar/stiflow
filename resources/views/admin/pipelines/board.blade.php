<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Pipeline Kanban - {{ $pipeline?->name ?? 'Default' }}
            </h2>
            <div class="flex gap-2">
                <select class="border border-gray-300 dark:border-gray-600 rounded-lg text-sm px-3 py-2 bg-white dark:bg-gray-800">
                    @forelse($pipelines ?? [] as $pl)
                        <option value="{{ $pl->id }}" @selected($pl->id === ($pipeline?->id ?? null))>{{ $pl->name }}</option>
                    @empty
                        <option>Belum ada pipeline</option>
                    @endforelse
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8">
            @livewire(\App\Livewire\PipelineKanbanBoard::class, ['pipeline' => $pipeline])
        </div>
    </div>
</x-app-layout>
