<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Komisi Sudah Dibayar</h3>
            <p class="text-sm text-gray-500">Komisi yang sudah masuk ke payout dan dibayarkan ke rekening Anda.</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500">Total Sudah Dibayar</p>
            <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($totalPaid ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Tanggal Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Batch Payout</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-blue-700 uppercase tracking-wider">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($commissions as $entry)
                    <tr class="hover:bg-blue-50/30">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $entry->paid_at?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($entry->payout)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 font-mono">
                                {{ $entry->payout->batch_number }}
                            </span>
                            @else
                            <span class="text-gray-400 text-xs italic">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-medium text-indigo-700">
                                {{ $entry->order?->number ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Level {{ $entry->level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-semibold text-blue-700">
                                Rp {{ number_format($entry->amount, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Belum ada komisi yang dibayar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $commissions->links() }}
        </div>
    </div>
</div>
