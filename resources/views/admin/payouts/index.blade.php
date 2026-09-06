<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manajemen Payout Komisi</h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex gap-2 border-b border-gray-200 dark:border-gray-700">
                @php
                    $tabs = ['' => 'Semua', 'draft' => 'Draft', 'locked' => 'Locked', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected'];
                    $currentStatus = request('status', '');
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('admin.payouts.index', ['status' => $key]) }}"
                       class="px-4 py-2 text-sm font-semibold border-b-2 -mb-px {{ $currentStatus === $key ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">No. Payout</th>
                            <th class="px-6 py-3 text-left font-medium">Promotor</th>
                            <th class="px-6 py-3 text-right font-medium">Jumlah Item</th>
                            <th class="px-6 py-3 text-right font-medium">Total</th>
                            <th class="px-6 py-3 text-center font-medium">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($payouts as $p)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-3 font-mono font-bold text-gray-900 dark:text-white">#{{ $p->id }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-semibold">{{ $p->promoterProfile?->user?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $p->promoterProfile?->stifin_code ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-3 text-right font-semibold">{{ $p->items_count ?? 0 }} item</td>
                                <td class="px-6 py-3 text-right font-bold text-gray-900 dark:text-white">Rp {{ number_format((float)($p->total_amount ?? 0), 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-center"><x-status-badge :value="$p->status?->value ?? $p->status" /></td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('admin.payouts.show', $p) }}" class="text-blue-600 font-semibold hover:underline">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                        @if($payouts->isEmpty())
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada payout.</td></tr>
                        @endif
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $payouts->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
