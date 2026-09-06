<div x-data="{ init: function() { console.log('Kanban loaded'); } }">
    <div class="flex gap-4 overflow-x-auto pb-6">
        @forelse($stages as $stage)
            <div class="flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
                 data-stage-id="{{ $stage->id }}">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="font-bold text-gray-900 dark:text-white">{{ $stage->name }}</div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                        {{ $stage->contacts->count() }}
                    </span>
                </div>
                <div class="p-3 space-y-2 min-h-[300px] space-y-2"
                     x-on:dragover.prevent="$el.classList.add('ring-2', 'ring-blue-400')"
                     x-on:dragleave="$el.classList.remove('ring-2', 'ring-blue-400')"
                     x-on:drop="
                        $el.classList.remove('ring-2', 'ring-blue-400');
                        const contactId = event.dataTransfer.getData('contactId');
                        const fromStageId = event.dataTransfer.getData('fromStageId');
                        if (contactId) {
                            $wire.moveContact(parseInt(contactId), parseInt(fromStageId), parseInt({{ $stage->id }}), 0);
                        }
                     ">
                    @forelse($stage->contacts as $contact)
                        <div draggable="true"
                             x-on:dragstart="
                                event.dataTransfer.setData('contactId', '{{ $contact->id }}');
                                event.dataTransfer.setData('fromStageId', '{{ $stage->id }}');
                             "
                             class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md hover:border-blue-400 transition cursor-grab active:cursor-grabbing">
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">{{ $contact->full_name }}</div>
                            <div class="text-xs text-gray-500 mt-1 truncate">{{ $contact->email ?? $contact->phone ?? '-' }}</div>
                            @if($contact->ownerPromoterProfile?->stifin_code)
                                <div class="text-[10px] font-mono text-indigo-600 mt-1">{{ $contact->ownerPromoterProfile->stifin_code }}</div>
                            @endif
                            @if($contact->estimated_value)
                                <div class="text-xs font-bold text-green-600 mt-2">Rp {{ number_format((float)$contact->estimated_value, 0, ',', '.') }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-xs text-gray-400 py-8">Drop card di sini</div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="w-full bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 p-12 text-center text-gray-500">
                Pipeline belum memiliki stage.
            </div>
        @endforelse
    </div>
</div>
