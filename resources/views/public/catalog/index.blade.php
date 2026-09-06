<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog Produk — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">
<div class="min-h-screen flex flex-col">
    <header class="py-4 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-600 to-emerald-500 flex items-center justify-center text-white font-black">SF</div>
                <div>
                    <h1 class="font-extrabold text-lg text-slate-900">STIFLOW</h1>
                </div>
            </a>
            <a href="{{ url('/') }}" class="text-sm text-slate-600 hover:text-indigo-600 font-medium">← Kembali</a>
        </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-900 mb-2">Katalog Produk</h2>
            <p class="text-slate-600">Pilih produk sesuai kebutuhan Anda.</p>
        </div>

        <div class="flex flex-wrap gap-3 mb-8 items-center">
            <form method="GET" class="flex flex-wrap gap-3 items-center">
                <select name="type" class="rounded-lg border-slate-300 text-sm">
                    <option value="">Semua Tipe</option>
                    <option value="voucher" @selected(request('type') === 'voucher')>Voucher</option>
                    <option value="course" @selected(request('type') === 'course')>Kursus</option>
                    <option value="digital" @selected(request('type') === 'digital')>Digital</option>
                    <option value="service" @selected(request('type') === 'service')>Layanan</option>
                    <option value="membership" @selected(request('type') === 'membership')>Membership</option>
                </select>
                <select name="sort" class="rounded-lg border-slate-300 text-sm">
                    <option value="default" @selected(request('sort') === 'default')>Default</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Harga Termurah</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Harga Termahal</option>
                    <option value="newest" @selected(request('sort') === 'newest')>Terbaru</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg">Filter</button>
            </form>
        </div>

        @if($products->count() === 0)
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center">
                <p class="text-slate-500">Belum ada produk yang dipublikasikan.</p>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                    <a href="{{ route('public.catalog.show', $product) }}" class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        <div class="aspect-video bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                            @if($product->featured_image_path)
                                <img src="{{ Storage::url($product->featured_image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-5xl opacity-30">📦</span>
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-bold uppercase tracking-wide px-2 py-1 bg-indigo-50 text-indigo-700 rounded-full">{{ $product->type }}</span>
                            </div>
                            <h3 class="font-bold text-lg text-slate-900 group-hover:text-indigo-700 mb-2 line-clamp-2">{{ $product->name }}</h3>
                            <p class="text-sm text-slate-500 line-clamp-2 mb-4 min-h-[2.5rem]">{{ $product->meta_description ?? $product->description }}</p>
                            <div class="flex items-center justify-between">
                                <p class="text-2xl font-black text-slate-900">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
                                <span class="text-sm font-semibold text-indigo-600 group-hover:translate-x-1 transition-transform">Detail →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @endif
    </main>
</div>
</body>
</html>
