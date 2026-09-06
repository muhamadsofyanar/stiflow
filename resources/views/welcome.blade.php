<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'STIFLOW') }} — Cabang {{ $branch?->branch_code ?? 'STIFIN' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-indigo-50 to-emerald-50 min-h-screen">
<div class="min-h-screen flex flex-col">
    <header class="py-4 px-4 sm:px-6 lg:px-8 bg-white/70 backdrop-blur-md border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-600 to-emerald-500 flex items-center justify-center text-white font-black tracking-tight shadow-md">
                    SF
                </div>
                <div>
                    <h1 class="font-extrabold text-lg text-slate-900 leading-none">STIFLOW</h1>
                    <p class="text-xs text-slate-500 leading-none mt-1">Voucher Platform Cabang</p>
                </div>
            </div>
            <nav class="flex gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 shadow">Dashboard →</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-white hover:border-slate-400">Masuk</a>
                    <a href="{{ route('login') }}#promotor" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 shadow">Promotor</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
        <section class="grid lg:grid-cols-2 gap-10 items-center mb-16">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold mb-5">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Cabang Aktif · Kode <strong class="tracking-wider">{{ $branch?->branch_code ?? '—' }}</strong>
                </div>
                <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-slate-900 leading-tight mb-5">
                    Topup Voucher <span class="text-transparent bg-clip-text bg-gradient-to-br from-indigo-600 to-emerald-500">STIFIN</span>,
                    Lebih Cepat.
                </h2>
                <p class="text-slate-600 text-lg mb-8 leading-relaxed max-w-lg">
                    Kelola order voucher promotor, verifikasi pembayaran, dan otomatis kirim saldo STIFIN
                    tanpa ketik manual lagi. Untuk {{ $branch?->brand_name ?? 'Cabang Anda' }}.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}#admin-login" class="px-6 py-3.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 shadow-lg shadow-slate-900/20">
                        Login Admin Cabang
                    </a>
                    <a href="{{ route('login') }}#promotor-login" class="px-6 py-3.5 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 shadow-lg shadow-emerald-600/20">
                        Saya Promotor →
                    </a>
                </div>
            </div>
            <div class="bg-white rounded-3xl shadow-2xl shadow-indigo-900/10 p-8 border border-slate-100">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="p-4 rounded-2xl bg-indigo-50 text-indigo-900 border border-indigo-100">
                        <p class="text-xs font-semibold uppercase tracking-wide opacity-75 mb-1">Voucher Preset</p>
                        <p class="text-3xl font-black">1 · 5 · 10</p>
                        <p class="text-xs opacity-70 mt-1">25 &amp; 50 unit juga tersedia</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-900 border border-emerald-100">
                        <p class="text-xs font-semibold uppercase tracking-wide opacity-75 mb-1">Harga Satuan</p>
                        @if($voucherProduct)
                            <p class="text-3xl font-black">Rp{{ number_format($voucherProduct->price/1000,0,',','.') }}rb</p>
                        @else
                            <p class="text-3xl font-black">Rp100rb</p>
                        @endif
                        <p class="text-xs opacity-70 mt-1">per unit voucher STIFIN</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Rekening Pembayaran Cabang</p>
                    @if($branch)
                        <div class="flex justify-between items-baseline py-2 border-b border-slate-100 last:border-0">
                            <span class="text-slate-500 text-sm">Bank</span>
                            <span class="font-bold text-slate-900">{{ $branch->bank_name }}</span>
                        </div>
                        <div class="flex justify-between items-baseline py-2 border-b border-slate-100 last:border-0">
                            <span class="text-slate-500 text-sm">No. Rekening</span>
                            <span class="font-mono text-lg font-black tracking-wider text-indigo-700">{{ $branch->bank_account }}</span>
                        </div>
                        <div class="flex justify-between items-baseline py-2">
                            <span class="text-slate-500 text-sm">Atas Nama</span>
                            <span class="font-bold text-slate-900 text-right">{{ $branch->bank_account_name }}</span>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Belum ada data cabang.</p>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid sm:grid-cols-3 gap-5 mb-12">
            <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <div class="h-11 w-11 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg mb-4">1</div>
                <h3 class="font-bold text-lg mb-2">Promotor Order</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Pilih jumlah voucher preset, bayar ke rekening cabang, upload bukti transfer.</p>
            </div>
            <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <div class="h-11 w-11 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg mb-4">2</div>
                <h3 class="font-bold text-lg mb-2">Admin Verifikasi</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Admin cek bukti bayar, 1 klik Approve. Data Outbox otomatis tersimpan aman di DB.</p>
            </div>
            <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
                <div class="h-11 w-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg mb-4">3</div>
                <h3 class="font-bold text-lg mb-2">Saldo Terkirim</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Queue worker kirim ke STIFIN Pusat API. Kalau timeout → NeedsReview, TIDAK double.</p>
            </div>
        </section>
    </main>

    <footer class="py-8 px-4 sm:px-6 lg:px-8 border-t border-slate-200 bg-white/60 backdrop-blur">
        <div class="max-w-6xl mx-auto text-sm text-slate-500 flex flex-col sm:flex-row gap-3 sm:justify-between sm:items-center">
            <p>{{ $brand?->footer_text ?? '© STIFLOW — Modular Monolith per Cabang.' }}</p>
            <div class="flex gap-4">
                <p>Kontak: {{ $branch?->contact ?? '-' }}</p>
                <p>{{ $branch?->branch_code ?? '-' }}</p>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
