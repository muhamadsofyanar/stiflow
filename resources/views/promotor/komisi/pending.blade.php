<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Komisi Pending</h3>
            <p class="text-sm text-gray-500">Komisi yang masih dalam masa tunggu verifikasi.</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500">Total Pending</p>
            <p class="text-2xl font-bold text-amber-600">Rp {{ number_format($totalPending ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-amber-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-amber-700 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-amber-700 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-amber-700 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-amber-700 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-amber-700 uppercase tracking-wider">Rate</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-amber-700 uppercase tracking-wider">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($commissions as $entry)
                    @php
                        $snapshot = $entry->orderItem?->product_snapshot_json ?? [];
                    @endphp
                    <tr class="hover:bg-amber-50/30">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $entry->earned_at?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm font-medium text-indigo-700">
                                {{ $entry->order?->number ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $snapshot['name'] ?? 'Produk' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Level {{ $entry->level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($entry->rate_type === \App\Enums\CommissionRuleType::Percentage)
                                {{ $entry->rate_value }}%
                            @else
                                Rp {{ number_format($entry->rate_value, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="text-sm font-semibold text-amber-700">
                                Rp {{ number_format($entry->amount, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            Belum ada komisi pending.
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
