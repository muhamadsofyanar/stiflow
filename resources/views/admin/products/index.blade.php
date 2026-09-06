<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Kelola Produk</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Produk</th>
                            <th class="px-6 py-3 text-left font-medium">Tipe</th>
                            <th class="px-6 py-3 text-right font-medium">Harga Unit</th>
                            <th class="px-6 py-3 text-center font-medium">Status</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($products as $prod)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $prod->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Min {{ $prod->voucherConfig?->min_qty ?? 1 }} · Max {{ $prod->voucherConfig?->max_qty ?? '∞' }} · Preset:
                                        @foreach($prod->voucherConfig?->presets_json ?? [] as $p) <span class="inline-block bg-gray-100 rounded px-1 mx-0.5 dark:bg-gray-700">{{ $p }}</span> @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 uppercase text-xs font-semibold">{{ $prod->type?->value ?? '-' }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format((float)($prod->voucherConfig?->unit_price ?? $prod->price), 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <x-status-badge :value="$prod->isActive() ? 'active' : 'inactive'" :label="$prod->isActive() ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('admin.products.toggle-active', $prod) }}">
                                        @csrf
                                        <button type="submit" class="text-xs rounded-md border px-3 py-1.5 font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50">
                                            {{ $prod->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">{{ $products->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
