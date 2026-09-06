<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Promotor</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-2xl p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-blue-100 text-xs uppercase tracking-wider">Kode STIFIN</div>
                            <div class="font-mono font-extrabold text-2xl mt-1">{{ $profile?->stifin_code ?? '-' }}</div>
                            <div class="text-blue-100 text-sm mt-1">{{ auth()->user()->name }}</div>
                        </div>
                        <div class="h-14 w-14 rounded-xl bg-white/10 flex items-center justify-center text-2xl font-bold">
                            @php
                                $code = $profile?->stifin_code ?? 'XX';
                                echo strtoupper(substr($code, 0, 2));
                            @endphp
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Siap membeli voucher? Klik tombol di bawah:</div>
                        <div class="mt-1 font-bold text-xl text-gray-900 dark:text-white">Pembelian voucher cepat & aman.</div>
                        <div class="mt-1 text-sm text-gray-500">
                            Transfer ke {{ $branch?->bank_name ?? '- Bank -' }} · {{ $branch?->bank_account ?? '-' }} a/n {{ $branch?->bank_account_name ?? '-' }}.
                        </div>
                    </div>
                    <a href="{{ route('promotor.checkout.index') }}"
                       class="mt-4 inline-flex items-center justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 shadow-sm w-full md:w-auto">
                        Beli Voucher Sekarang →
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Belum Bayar</div>
                    <div class="text-2xl font-extrabold text-gray-800 mt-1">{{ $stats['pending_payment'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Diproses</div>
                    <div class="text-2xl font-extrabold text-blue-700 mt-1">{{ $stats['processing'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Selesai</div>
                    <div class="text-2xl font-extrabold text-green-700 mt-1">{{ $stats['completed'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Perlu Review</div>
                    <div class="text-2xl font-extrabold text-red-600 mt-1">{{ $stats['needs_review'] }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm col-span-2 md:col-span-1">
                    <div class="text-xs uppercase tracking-wider text-gray-500">Total Belanja</div>
                    <div class="text-lg font-extrabold text-gray-900 dark:text-white mt-1 truncate">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 dark:text-white">Order Terbaru</h3>
                    <a href="{{ route('promotor.orders.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Lihat semua →</a>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">No. Order</th>
                            <th class="px-6 py-3 text-left font-medium">Detail</th>
                            <th class="px-6 py-3 text-right font-medium">Total</th>
                            <th class="px-6 py-3 text-center font-medium">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($recent as $o)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3 font-mono text-gray-900 dark:text-white">{{ $o->number }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $o->items->sum('quantity') }} voucher</div>
                                    <div class="text-xs text-gray-500">{{ $o->created_at }}</div>
                                </td>
                                <td class="px-6 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float)$o->total, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-center"><x-status-badge :value="$o->status" /></td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('promotor.orders.show', $o) }}" class="text-sm font-semibold text-blue-600 hover:underline">Detail →</a>
                                </td>
                            </tr>
                        @endforeach
                        @if($recent->isEmpty())
                            <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada order. <a href="{{ route('promotor.checkout.index') }}" class="text-blue-600 font-semibold underline">Beli voucher pertama →</a></td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
