<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Order Saya</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-5 flex flex-wrap gap-2">
                <a href="{{ route('promotor.orders.index') }}" class="px-3 py-1.5 rounded-md text-sm font-semibold {{ !$status ? 'bg-gray-800 text-white' : 'bg-white dark:bg-gray-800 border text-gray-700 dark:text-gray-200' }}">Semua</a>
                @foreach([
                    App\Enums\OrderStatus::PendingPayment,
                    App\Enums\OrderStatus::PaymentSubmitted,
                    App\Enums\OrderStatus::Paid,
                    App\Enums\OrderStatus::Fulfilling,
                    App\Enums\OrderStatus::Completed,
                    App\Enums\OrderStatus::NeedsReview,
                ] as $s)
                    <a href="{{ route('promotor.orders.index', ['status' => $s->value]) }}"
                       class="px-3 py-1.5 rounded-md text-sm font-semibold {{ $status == $s->value ? 'bg-gray-800 text-white' : 'bg-white dark:bg-gray-800 border text-gray-700 dark:text-gray-200' }}">
                        {{ $s->label() }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
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
                        @foreach($orders as $o)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-4 font-mono text-gray-900 dark:text-white">{{ $o->number }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $o->items->sum('quantity') }} voucher</div>
                                    <div class="text-xs text-gray-500">Dibuat: {{ $o->created_at }} · Kadaluarsa: {{ $o->expires_at ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float)$o->total, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center"><x-status-badge :value="$o->status" /></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('promotor.orders.show', $o) }}" class="text-sm font-semibold text-blue-600 hover:underline">Detail →</a>
                                </td>
                            </tr>
                        @endforeach
                        @if($orders->isEmpty())
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Tidak ada order.</td></tr>
                        @endif
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
