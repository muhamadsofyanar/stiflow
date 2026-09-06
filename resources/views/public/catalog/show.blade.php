<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->name }} — {{ config('app.name') }}</title>
    <meta name="description" content="{{ $product->meta_description ?? $product->description }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 min-h-screen">
<div class="min-h-screen flex flex-col">
    <header class="py-4 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="{{ route('public.catalog.index') }}" class="flex items-center gap-2">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-600 to-emerald-500 flex items-center justify-center text-white font-black">SF</div>
                <div>
                    <h1 class="font-extrabold text-lg text-slate-900">STIFLOW</h1>
                </div>
            </a>
            <a href="{{ route('public.catalog.index') }}" class="text-sm text-slate-600 hover:text-indigo-600 font-medium">← Kembali ke Katalog</a>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid lg:grid-cols-2 gap-10">
            <div>
                <div class="aspect-video bg-gradient-to-br from-slate-100 to-slate-200 rounded-3xl border border-slate-200 flex items-center justify-center overflow-hidden shadow-xl">
                    @if($product->featured_image_path)
                        <img src="{{ Storage::url($product->featured_image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-8xl opacity-30">📦</span>
                    @endif
                </div>
                @if($product->description)
                    <div class="mt-8 bg-white border border-slate-200 rounded-2xl p-6">
                        <h3 class="font-bold text-lg text-slate-900 mb-4">Deskripsi</h3>
                        <div class="prose prose-slate text-slate-600 whitespace-pre-line">{{ $product->description }}</div>
                    </div>
                @endif
            </div>

            <div>
                <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-lg sticky top-6">
                    <div class="mb-4">
                        <span class="inline-block text-xs font-bold uppercase tracking-wide px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full mb-3">{{ $product->type }}</span>
                        <h1 class="text-3xl font-black text-slate-900 mb-2">{{ $product->name }}</h1>
                    </div>

                    <div class="mb-6">
                        <p class="text-sm font-semibold text-slate-500 mb-1">Harga mulai dari</p>
                        <p class="text-4xl font-black text-slate-900">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
                    </div>

                    @if($product->variants->count() > 0)
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Varian</label>
                            <select id="variantSelect" class="w-full rounded-xl border-slate-300">
                                @foreach($product->variants as $variant)
                                    <option value="{{ $variant->id }}" data-price="{{ $variant->price_amount }}">
                                        {{ $variant->name }} — Rp{{ number_format($variant->price_amount, 0, ',', '.') }}
                                        @if($variant->sku) ({{ $variant->sku }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button class="w-full py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold text-lg rounded-2xl shadow-lg shadow-indigo-600/20 transition-all">
                        BELI SEKARANG →
                    </button>

                    @if($product->orderBumps->count() > 0)
                        <div class="mt-8 space-y-3">
                            <p class="text-sm font-bold text-slate-700 uppercase tracking-wide">Penawaran Spesial:</p>
                            @foreach($product->orderBumps as $bump)
                                <label class="flex gap-3 p-4 border border-indigo-200 bg-indigo-50 rounded-xl cursor-pointer hover:bg-indigo-100 transition-colors">
                                    <input type="checkbox" class="mt-1 rounded border-indigo-400 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900">{{ $bump->title }}</p>
                                        <p class="text-sm text-slate-600 mb-1">{{ $bump->description }}</p>
                                        <p class="font-bold text-indigo-700">+ Rp{{ number_format($bump->price_addition_amount, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
