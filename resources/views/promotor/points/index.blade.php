<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Poin Reward Saya</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-gradient-to-br from-pink-500 via-rose-500 to-red-500 text-white rounded-3xl p-8 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-pink-100 text-sm uppercase tracking-wider">Total Poin</div>
                        <div class="font-black text-5xl mt-2">{{ number_format($balance ?? 0, 0, ',', '.') }}</div>
                        <div class="text-pink-100 mt-2">Kumpulkan poin dari setiap transaksi & aktivitas.</div>
                    </div>
                    <div class="h-20 w-20 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center text-5xl">
                        ⭐
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 font-bold text-gray-900 dark:text-white">
                    Riwayat Mutasi Poin
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Tanggal</th>
                            <th class="px-6 py-3 text-left font-medium">Tipe Transaksi</th>
                            <th class="px-6 py-3 text-left font-medium">Keterangan</th>
                            <th class="px-6 py-3 text-right font-medium">Masuk</th>
                            <th class="px-6 py-3 text-right font-medium">Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($entries as $e)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3 text-gray-600">{{ $e->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-700 font-mono">
                                        {{ $e->entry_type?->value ?? $e->entry_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-300">{{ $e->notes ?? '-' }}</td>
                                <td class="px-6 py-3 text-right font-bold text-green-600">
                                    {{ ($e->direction?->value ?? $e->direction) === 'credit' ? '+'.number_format((float)$e->amount_points, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-3 text-right font-bold text-red-600">
                                    {{ ($e->direction?->value ?? $e->direction) === 'debit' ? '-'.number_format((float)$e->amount_points, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                        @if($entries->isEmpty())
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada riwayat poin.</td></tr>
                        @endif
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $entries->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
