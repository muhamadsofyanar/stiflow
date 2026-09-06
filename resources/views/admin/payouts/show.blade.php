<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Detail Payout #{{ $payout->id }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.payouts.index') }}" class="inline-flex items-center px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('status'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl p-4 text-green-700 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 dark:text-white mb-4">Info Payout</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd><x-status-badge :value="$payout->status?->value ?? $payout->status" /></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Promotor</dt><dd class="font-semibold">{{ $payout->promoterProfile?->user?->name ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Kode STIFIN</dt><dd class="font-mono">{{ $payout->promoterProfile?->stifin_code ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Total Komisi</dt><dd class="font-bold text-lg">Rp {{ number_format((float)($payout->total_amount ?? 0), 0, ',', '.') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Tanggal Dibuat</dt><dd>{{ $payout->created_at?->format('Y-m-d H:i') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Terkunci</dt><dd>{{ $payout->locked_at?->format('Y-m-d H:i') ?? '-' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Disetujui</dt><dd>{{ $payout->approved_at?->format('Y-m-d H:i') ?? '-' }}</dd></div>
                    </dl>

                    <div class="mt-6 space-y-2">
                        @livewire(\App\Livewire\PayoutLockConfirm::class, ['payout' => $payout], key('lock-'.$payout->id))

                        @if(($payout->status?->value ?? $payout->status) === 'locked' && (auth()->user()->hasPermission('payouts.approve') || auth()->user()->isAdmin()))
                            <form method="POST" action="{{ route('admin.payouts.approve', $payout) }}" onsubmit="return confirm('Setujui payout ini?')">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700">
                                    ✓ Setujui Payout
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 font-bold text-gray-900 dark:text-white">
                        Item Komisi ({{ $payout->items->count() }})
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Order / Sumber</th>
                                <th class="px-6 py-3 text-left font-medium">Tipe Komisi</th>
                                <th class="px-6 py-3 text-right font-medium">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($payout->items as $item)
                                <tr>
                                    <td class="px-6 py-3">
                                        <div class="font-mono text-xs">{{ $item->commissionEntry?->order?->number ?? '#' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->commissionEntry?->rule_type?->value ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ $item->commissionEntry?->description ?? $item->description ?? '-' }}</td>
                                    <td class="px-6 py-3 text-right font-bold">Rp {{ number_format((float)($item->amount ?? 0), 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">Belum ada item komisi.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700/50 font-bold">
                            <tr>
                                <td colspan="2" class="px-6 py-3 text-right">TOTAL</td>
                                <td class="px-6 py-3 text-right">Rp {{ number_format((float)($payout->total_amount ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
