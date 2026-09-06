<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Katalog Produk</h2><a href="{{ route('admin.products-catalog.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Tambah Produk</a></div></x-slot>
    <div class="py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-sm"><thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-6 py-3 text-left">Produk</th><th class="px-6 py-3 text-left">Tipe</th><th class="px-6 py-3 text-left">Harga</th><th class="px-6 py-3 text-left">Status</th><th class="px-6 py-3"></th></tr></thead><tbody class="divide-y dark:divide-gray-700">
        @forelse($products as $product)
            <tr><td class="px-6 py-4 font-semibold">{{ $product->name }}</td><td class="px-6 py-4">{{ $product->type?->value ?? $product->type }}</td><td class="px-6 py-4">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</td><td class="px-6 py-4">{{ $product->status?->value ?? $product->status }}</td><td class="px-6 py-4 text-right"><a class="text-blue-600" href="{{ route('admin.products-catalog.show', $product) }}">Detail</a> · <a class="text-amber-600" href="{{ route('admin.products-catalog.edit', $product) }}">Edit</a></td></tr>
        @empty<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada produk.</td></tr>@endforelse
        </tbody></table><div class="px-6 py-4">{{ $products->links() }}</div>
    </div></div></div>
</x-app-layout>
