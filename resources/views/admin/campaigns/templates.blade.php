<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Message Templates</h2>
            <a href="{{ route('admin.templates.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">+ Buat Template</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 text-green-700 dark:text-green-300 mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Nama Template</th>
                            <th class="px-6 py-3 text-left font-medium">Channel</th>
                            <th class="px-6 py-3 text-left font-medium">Subject</th>
                            <th class="px-6 py-3 text-center font-medium">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($templates as $t)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">{{ $t->name }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200 rounded text-xs font-mono">{{ $t->channel?->value ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $t->subject_line ?? '-' }}</td>
                                <td class="px-6 py-3 text-center">
                                    @if($t->is_active)
                                        <span class="px-2 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded text-xs font-bold">AKTIF</span>
                                    @else
                                        <span class="text-xs text-gray-400">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-right space-x-2">
                                    <a href="{{ route('admin.templates.show', $t) }}" class="text-blue-600 font-semibold hover:underline">Lihat</a>
                                    <a href="{{ route('admin.templates.edit', $t) }}" class="text-amber-600 font-semibold hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.templates.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus template?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 font-semibold hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @if($templates->isEmpty())
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada template.</td></tr>
                        @endif
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
