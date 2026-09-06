<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Katalog — Admin {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin Panel</span>
            <span class="text-slate-400">/ Manajemen Katalog</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-300 hover:text-white">← Dashboard</a>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Manajemen Katalog Produk</h1>
                <p class="text-slate-600">Kelola publikasi, urutan, dan gambar unggulan produk.</p>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('status') }}</div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $product)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-600 font-mono">#{{ $product->sort_order }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($product->featured_image_path)
                                        <div class="h-12 w-12 rounded-lg bg-slate-100 overflow-hidden">
                                            <img src="{{ Storage::url($product->featured_image_path) }}" alt="" class="h-full w-full object-cover">
                                        </div>
                                    @else
                                        <div class="h-12 w-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">📦</div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-500">slug: {{ $product->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold uppercase px-2 py-1 bg-indigo-50 text-indigo-700 rounded-full">{{ $product->type }}</span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                @if($product->is_published)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Draft
                                    </span>
                                @endif
                                @if(!$product->is_catalog_visible)
                                    <span class="text-xs font-bold px-2 py-1 bg-amber-50 text-amber-700 rounded-full ml-1">Hidden</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                <a href="{{ route('admin.catalog.edit', $product) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Edit</a>
                                @if($product->is_published)
                                    <form method="POST" action="{{ route('admin.catalog.unpublish', $product) }}" class="inline">
                                        @csrf
                                        <button class="text-slate-600 hover:text-slate-800 font-semibold">Unpublish</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.catalog.publish', $product) }}" class="inline">
                                        @csrf
                                        <button class="text-emerald-600 hover:text-emerald-800 font-semibold">Publish</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </div>
</div>
</body>
</html>
