<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lisensi Aplikasi — Admin {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin / Lisensi Aplikasi</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-300 hover:text-white">← Dashboard</a>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-black text-slate-900 mb-6">🔑 Lisensi Aplikasi</h1>

        @if(session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl">
                @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h2 class="font-bold text-lg text-slate-900 mb-4">🧩 Installation Fingerprint</h2>
                <p class="text-xs text-slate-500 mb-2 uppercase tracking-wide font-bold">Installation UUID</p>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 font-mono text-xs break-all select-all text-slate-700">
                    {{ $installationUuid }}
                </div>
                <p class="text-xs text-slate-500 mt-4">UUID ini unik per instalasi. Kirim ke vendor untuk mengaktifkan lisensi.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <h2 class="font-bold text-lg text-slate-900 mb-4">💳 Informasi Lisensi</h2>
                @if($license)
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-500 text-sm">Tier</span>
                            <span class="font-bold text-indigo-700">{{ $license->tier->value }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 text-sm">Status</span>
                            @if($license->isActive())
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold">Active</span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-full text-xs font-bold">{{ $license->status->value }}</span>
                            @endif
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 text-sm">Seats</span>
                            <span class="font-bold">{{ $license->seats_allowed }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 text-sm">Max Cabang</span>
                            <span class="font-bold">{{ $license->max_branches }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 text-sm">Customer</span>
                            <span class="font-bold">{{ $license->customer_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 text-sm">Aktif Sejak</span>
                            <span class="text-sm">{{ $license->activated_at?->format('d M Y') ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 text-sm">Berlaku Hingga</span>
                            <span class="text-sm font-semibold {{ $license->hasExpired() ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ $license->expires_at?->format('d M Y') ?? 'Lifetime' }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6 text-slate-500">
                        <p class="mb-2">Belum ada lisensi aktif.</p>
                        <p class="text-xs">Aktifkan menggunakan license key di bawah ini.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6 bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h2 class="font-bold text-lg text-slate-900 mb-4">🔐 Aktivasi Lisensi</h2>
            <form method="POST" action="{{ route('admin.license.activate') }}" class="flex gap-3">
                @csrf
                <input type="text" name="license_key" placeholder="Masukkan license key (XXXX-XXXX-XXXX-XXXX)" class="flex-1 rounded-xl border-slate-300 font-mono">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-emerald-600 hover:from-indigo-700 hover:to-emerald-700 text-white font-bold rounded-xl shadow whitespace-nowrap">
                    Aktifkan Lisensi
                </button>
            </form>
            <p class="text-xs text-slate-500 mt-3">License key akan di-hash SHA-256 sebelum divalidasi ke database. Tidak ada plaintext tersimpan.</p>
        </div>
    </div>
</div>
</body>
</html>
