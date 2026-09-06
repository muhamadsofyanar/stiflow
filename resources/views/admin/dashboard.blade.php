<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Admin</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Menunggu Verif</div>
                    <div class="text-2xl font-extrabold text-yellow-600 mt-1">{{ $stats['pending_proof'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Order Hari Ini</div>
                    <div class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['total_orders_today'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Selesai</div>
                    <div class="text-2xl font-extrabold text-green-600 mt-1">{{ $stats['completed_orders'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Rekonsiliasi</div>
                    <div class="text-2xl font-extrabold text-red-600 mt-1">{{ $stats['open_reconciliations'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-indigo-100 dark:border-indigo-900/50 shadow-sm ring-1 ring-indigo-50/70">
                    <div class="text-xs uppercase tracking-wider text-indigo-500 font-semibold">Total Promotor</div>
                    <div class="text-3xl font-black text-indigo-700 mt-1">{{ $stats['total_promotors'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Promotor Aktif</div>
                    <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['active_promotors'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-amber-100 dark:border-amber-900/50 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-amber-600 font-semibold">Pending Verif</div>
                    <div class="text-2xl font-extrabold text-amber-700 mt-1">{{ $stats['pending_promotors'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Pendapatan</div>
                    <div class="text-lg font-extrabold text-green-700 mt-1">Rp{{ number_format($stats['total_revenue_completed'], 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100">Order Terbaru</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Lihat semua →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">No. Order</th>
                                <th class="px-6 py-3 text-left font-medium">Promotor</th>
                                <th class="px-6 py-3 text-right font-medium">Total</th>
                                <th class="px-6 py-3 text-center font-medium">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-3 font-mono text-gray-900 dark:text-white">{{ $order->number }}</td>
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $order->user?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->user?->promoterProfile?->stifin_code ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float)$order->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-center"><x-status-badge :value="$order->status" /></td>
                                    <td class="px-6 py-3 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-semibold text-blue-600 hover:underline">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                            @if($recentOrders->isEmpty())
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada order.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
