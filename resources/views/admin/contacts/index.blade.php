<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">CRM - Kontak</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.pipelines.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Kanban Board</a>
                <a href="{{ route('admin.contacts.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">+ Tambah Kontak</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Nama</th>
                                <th class="px-6 py-3 text-left font-medium">Email / Phone</th>
                                <th class="px-6 py-3 text-left font-medium">Owner</th>
                                <th class="px-6 py-3 text-left font-medium">Stage</th>
                                <th class="px-6 py-3 text-center font-medium">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($contacts as $c)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">{{ $c->full_name }}</td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                        <div>{{ $c->email ?? '-' }}</div>
                                        <div class="text-xs">{{ $c->phone ?? $c->whatsapp ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $c->ownerPromoterProfile?->stifin_code ?? '-' }}</td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $c->stage?->name ?? '-' }}</td>
                                    <td class="px-6 py-3 text-center"><x-status-badge :value="$c->status?->value ?? $c->status" /></td>
                                    <td class="px-6 py-3 text-right space-x-2">
                                        <a href="{{ route('admin.contacts.show', $c) }}" class="text-blue-600 font-semibold hover:underline">Detail</a>
                                        <a href="{{ route('admin.contacts.edit', $c) }}" class="text-amber-600 font-semibold hover:underline">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                            @if($contacts->isEmpty())
                                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada kontak.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $contacts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
