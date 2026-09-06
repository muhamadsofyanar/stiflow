<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Katalog — {{ $product->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin</span>
            <span class="text-slate-400">/ Katalog / Edit</span>
        </div>
        <a href="{{ route('admin.catalog.index') }}" class="text-sm text-slate-300 hover:text-white">← Kembali</a>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-black text-slate-900 mb-6">Edit Pengaturan Katalog: {{ $product->name }}</h1>

        <form method="POST" action="{{ route('admin.catalog.update', $product) }}" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-2xl p-8 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Nama Produk</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full rounded-xl border-slate-300" required>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Panjang</label>
                <textarea name="description" rows="4" class="w-full rounded-xl border-slate-300">{{ old('description', $product->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Meta Description (untuk SEO & kartu)</label>
                <textarea name="meta_description" rows="2" class="w-full rounded-xl border-slate-300">{{ old('meta_description', $product->meta_description) }}</textarea>
                <p class="text-xs text-slate-500 mt-1">Maks 160 karakter, tampil di hasil pencarian & card katalog.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}" min="0" class="w-full rounded-xl border-slate-300">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_catalog_visible" @checked(old('is_catalog_visible', $product->is_catalog_visible)) class="rounded border-slate-400 text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                        <span class="text-sm font-bold text-slate-700">Tampilkan di Katalog Publik</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Gambar Unggulan (Featured Image)</label>
                @if($product->featured_image_path)
                    <div class="mb-3">
                        <img src="{{ Storage::url($product->featured_image_path) }}" alt="" class="h-40 rounded-xl border border-slate-200">
                    </div>
                @endif
                <input type="file" name="featured_image" accept="image/*" class="w-full rounded-xl border-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-slate-500 mt-1">Maks 5MB, format: JPG/PNG/WebP.</p>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-200">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow">Simpan Perubahan</button>
                <a href="{{ route('admin.catalog.index') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
