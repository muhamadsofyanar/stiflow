<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Social Proof Settings — Admin {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin / Social Proof</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-300 hover:text-white">← Dashboard</a>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('status') }}</div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-black text-slate-900">🔥 Social Proof per Produk</h1>
            <p class="text-slate-600">Tampilkan notifikasi pembelian terbaru untuk meningkatkan konversi.</p>
        </div>

        <div class="space-y-4">
            @foreach($products as $product)
                @php($setting = $product->socialProofSetting)
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                    <form method="POST" action="{{ route('admin.social-proof.update', $product) }}">
                        @csrf
                        <div class="flex items-center gap-4 mb-5 pb-5 border-b border-slate-100">
                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center text-white text-xl">📦</div>
                            <div class="flex-1">
                                <p class="font-bold text-lg text-slate-900">{{ $product->name }}</p>
                                <p class="text-sm text-slate-500 font-mono">slug: {{ $product->slug }}</p>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="show_recent_purchase" @checked(old('show_recent_purchase', $setting?->show_recent_purchase ?? true)) class="rounded border-slate-400 text-emerald-600 focus:ring-emerald-500 w-5 h-5">
                                <span class="font-semibold text-slate-700">Aktifkan</span>
                            </label>
                        </div>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Window (Jam)</label>
                                <input type="number" name="time_window_hours" value="{{ old('time_window_hours', $setting?->time_window_hours ?? 48) }}" min="1" max="720" class="w-full rounded-xl border-slate-300">
                                <p class="text-xs text-slate-500 mt-1">Tampilkan order dalam X jam terakhir.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Batas Tampil</label>
                                <input type="number" name="display_limit" value="{{ old('display_limit', $setting?->display_limit ?? 10) }}" min="1" max="100" class="w-full rounded-xl border-slate-300">
                                <p class="text-xs text-slate-500 mt-1">Maks item ditampilkan.</p>
                            </div>
                            <div>
                                <label class="flex items-center gap-2 pt-6 cursor-pointer">
                                    <input type="checkbox" name="anonymize_name" @checked(old('anonymize_name', $setting?->anonymize_name ?? true)) class="rounded border-slate-400 text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                    <span class="font-semibold text-slate-700">Samarkan Nama</span>
                                </label>
                                <p class="text-xs text-slate-500 mt-1">Budi S. → B**i S.</p>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl shadow">Simpan</button>
                        </div>
                    </form>
                </div>
            @endforeach
            @if($products->count() === 0)
                <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-500">Belum ada produk.</div>
            @endif
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </div>
</div>
</body>
</html>
