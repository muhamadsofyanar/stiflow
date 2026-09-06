<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Komisi Saya</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-yellow-500 to-amber-600 text-white rounded-2xl p-5 shadow">
                    <div class="text-xs uppercase tracking-wider text-yellow-100">Pending</div>
                    <div class="text-2xl font-black mt-1">Rp {{ number_format((float)($totals['pending'] ?? 0), 0, ',', '.') }}</div>
                </div>
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-2xl p-5 shadow">
                    <div class="text-xs uppercase tracking-wider text-blue-100">Payable</div>
                    <div class="text-2xl font-black mt-1">Rp {{ number_format((float)($totals['payable'] ?? 0), 0, ',', '.') }}</div>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-2xl p-5 shadow">
                    <div class="text-xs uppercase tracking-wider text-green-100">Paid</div>
                    <div class="text-2xl font-black mt-1">Rp {{ number_format((float)($totals['paid'] ?? 0), 0, ',', '.') }}</div>
                </div>
                <div class="bg-gradient-to-br from-gray-500 to-slate-600 text-white rounded-2xl p-5 shadow">
                    <div class="text-xs uppercase tracking-wider text-gray-200">Reversed</div>
                    <div class="text-2xl font-black mt-1">Rp {{ number_format((float)($totals['reversed'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 dark:border-gray-700 flex">
                    @foreach($tabs as $key => $label)
                        <a href="{{ route('promotor.komisi.index', ['tab' => $key]) }}"
                           class="px-6 py-3 text-sm font-semibold border-b-2 -mb-px {{ $activeTab === $key ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Tanggal</th>
                                <th class="px-6 py-3 text-left font-medium">Order / Sumber</th>
                                <th class="px-6 py-3 text-left font-medium">Tipe Komisi</th>
                                <th class="px-6 py-3 text-right font-medium">Jumlah</th>
                                <th class="px-6 py-3 text-center font-medium">Status</th>
                                <th class="px-6 py-3 text-left font-medium">Payout</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($commissions as $c)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-3 text-gray-600">{{ $c->created_at?->format('Y-m-d') }}</td>
                                    <td class="px-6 py-3">
                                        <div class="font-mono text-xs font-bold">#{{ $c->order?->number ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                        <div class="text-xs font-mono">{{ $c->rule_type?->value ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $c->description ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-right font-bold text-green-700 dark:text-green-400">+ Rp {{ number_format((float)($c->amount ?? 0), 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-center"><x-status-badge :value="$c->status?->value ?? $c->status" /></td>
                                    <td class="px-6 py-3 text-xs text-gray-500">
                                        {{ $c->payout ? '#Payout-'.$c->payout_id : 'Belum diproses' }}
                                    </td>
                                </tr>
                            @endforeach
                            @if($commissions->isEmpty())
                                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada komisi di tab ini.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $commissions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
