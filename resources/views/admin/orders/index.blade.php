<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Kelola Order</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-5 grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama / email / no order" class="md:col-span-2 rounded-lg border px-3 py-2 dark:bg-gray-800 dark:text-white dark:border-gray-700" />
                <select name="status" class="rounded-lg border px-3 py-2 dark:bg-gray-800 dark:text-white dark:border-gray-700">
                    <option value="">Semua Status</option>
                    @foreach(App\Enums\OrderStatus::cases() as $s)
                        <option value="{{ $s->value }}" @selected($status == $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900 flex-1">Cari</button>
                    <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-lg border text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">No. Order</th>
                                <th class="px-6 py-3 text-left font-medium">Promotor</th>
                                <th class="px-6 py-3 text-left font-medium">Qty</th>
                                <th class="px-6 py-3 text-right font-medium">Total</th>
                                <th class="px-6 py-3 text-center font-medium">Status</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-3 font-mono text-gray-900 dark:text-white">{{ $order->number }}</td>
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $order->user?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $order->user?->email ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $order->items->sum('quantity') }} voucher
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float)$order->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-center"><x-status-badge :value="$order->status" /></td>
                                    <td class="px-6 py-3 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-semibold text-blue-600 hover:underline">Detail →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
