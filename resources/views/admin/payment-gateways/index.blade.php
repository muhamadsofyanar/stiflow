<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Gateway — Admin {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin / Payment Gateways</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-300 hover:text-white">← Dashboard</a>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('status') }}</div>
        @endif

        <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-black text-slate-900">💳 Payment Gateway Config</h1>
                <p class="text-slate-600">Kelola penyedia pembayaran untuk checkout.</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
            <h3 class="font-bold text-slate-900 mb-4">+ Tambah Gateway Baru</h3>
            <form method="POST" action="{{ route('admin.payment-gateways.store') }}" class="grid md:grid-cols-4 gap-3">
                @csrf
                <select name="provider" class="rounded-xl border-slate-300" required>
                    <option value="">Pilih Provider</option>
                    <option value="Xendit">Xendit</option>
                    <option value="Midtrans">Midtrans</option>
                    <option value="Finpay">Finpay</option>
                    <option value="ManualBankTransfer">Manual Bank Transfer</option>
                </select>
                <input type="text" name="display_name" placeholder="Nama Tampilan" class="rounded-xl border-slate-300" required>
                <input type="number" name="sort_order" value="0" min="0" class="rounded-xl border-slate-300" required>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow">Tambah</button>
            </form>
        </div>

        <div class="space-y-3">
            @foreach($gateways as $gw)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-center gap-4 shadow-sm">
                    <div class="h-14 w-14 rounded-2xl {{ $gw->is_active ? 'bg-gradient-to-br from-indigo-600 to-emerald-500' : 'bg-slate-100' }} flex items-center justify-center text-2xl {{ $gw->is_active ? 'text-white' : 'text-slate-400' }}">
                        💳
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-bold text-slate-900">{{ $gw->display_name }}</p>
                            <span class="text-xs font-bold uppercase tracking-wide px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">{{ $gw->provider->value }}</span>
                            @if($gw->is_active)
                                <span class="text-xs font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full">Aktif</span>
                            @else
                                <span class="text-xs font-bold px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full">Nonaktif</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 mt-1">
                            Fee: {{ $gw->percent_fee }}% + Rp{{ number_format($gw->fixed_fee, 0, ',', '.') }} · Min: Rp{{ number_format($gw->minimum_amount, 0, ',', '.') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.payment-gateways.toggle', $gw) }}">
                        @csrf
                        <button class="px-4 py-2 rounded-xl text-sm font-semibold {{ $gw->is_active ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow' }}">
                            {{ $gw->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                </div>
            @endforeach
            @if($gateways->count() === 0)
                <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-500">
                    <p>Belum ada payment gateway yang dikonfigurasi.</p>
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
