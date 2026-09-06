<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                CRM Kanban - {{ $pipeline?->name ?? 'Default Pipeline' }}
            </h2>
            <div class="flex gap-2">
                <select id="js-pipeline-switch"
                        data-base-url="{{ route('promotor.crm.boards') }}"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg text-sm px-3 py-2 bg-white dark:bg-gray-800">
                    <option value="">Pilih Pipeline...</option>
                    @foreach($pipelines ?? [] as $pl)
                        <option value="{{ $pl->id }}" @selected($pl->id === ($pipeline?->id ?? null))>{{ $pl->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-8">
            @if($stages->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 p-12 text-center text-gray-500">
                    Pipeline belum memiliki stage. Hubungi admin untuk mengatur pipeline CRM.
                </div>
            @else
                <div class="flex gap-4 overflow-x-auto pb-6">
                    @foreach($stages as $stage)
                        <div class="flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $stage->name }}</div>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                                        {{ $stage->contacts->count() }}
                                    </span>
                                </div>
                                @if($stage->win_probability)
                                    <div class="text-xs text-gray-500 mt-1">Win rate: {{ $stage->win_probability }}%</div>
                                @endif
                            </div>
                            <div class="p-3 space-y-2 min-h-[400px]">
                                @forelse($stage->contacts as $contact)
                                    <a href="{{ route('promotor.crm.contacts.show', $contact) }}"
                                       class="block bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md hover:border-blue-300 transition cursor-pointer">
                                        <div class="font-semibold text-sm text-gray-900 dark:text-white">{{ $contact->full_name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $contact->email ?? $contact->phone ?? '-' }}</div>
                                        @if($contact->estimated_value)
                                            <div class="text-xs font-bold text-green-600 mt-2">Rp {{ number_format((float)$contact->estimated_value, 0, ',', '.') }}</div>
                                        @endif
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="text-[10px] text-gray-400">{{ $contact->created_at?->diffForHumans() }}</span>
                                            @if($contact->next_followup_at)
                                                <span class="text-[10px] px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full">Followup</span>
                                            @endif
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center text-xs text-gray-400 py-8">Belum ada kontak di stage ini.</div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sel = document.getElementById('js-pipeline-switch');
            if (sel) {
                sel.addEventListener('change', function () {
                    var base = sel.getAttribute('data-base-url') || '';
                    if (sel.value) {
                        window.location.href = base + '/' + sel.value;
                    }
                });
            }
        });
    </script>
</x-app-layout>
