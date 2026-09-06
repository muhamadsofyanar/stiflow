<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unduhan — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-900 to-indigo-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <div class="bg-white border border-slate-200 rounded-3xl p-10 shadow-2xl">
            <div class="h-20 w-20 mx-auto mb-6 rounded-3xl bg-gradient-to-br from-rose-500 to-amber-500 flex items-center justify-center text-white text-4xl shadow-xl">
                ⚠️
            </div>
            <h1 class="text-3xl font-black text-slate-900 mb-3">Akses Ditolak</h1>
            <p class="text-slate-600 mb-8">Token unduhan tidak valid, sudah dicabut, atau batas unduh sudah tercapai.</p>
            <a href="{{ route('member.unduhan.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow">
                ← Kembali ke Daftar Unduhan
            </a>
        </div>
        <p class="mt-6 text-white/60 text-sm">Jika masalah berlanjut, hubungi administrator.</p>
    </div>
</body>
</html>
