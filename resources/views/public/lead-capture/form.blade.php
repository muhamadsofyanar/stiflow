<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Lead Capture - STIFLOW</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-indigo-950">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-lg">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-600 px-8 py-10 text-white">
                    <div class="text-xs uppercase tracking-widest text-indigo-100 mb-1">STIFLOW · LEAD CAPTURE</div>
                    <h1 class="text-2xl font-extrabold mt-1">Daftar & Dapatkan Hasil STIFIN</h1>
                    <p class="text-indigo-100 mt-2 text-sm">Isi data diri Anda di bawah ini untuk memulai.</p>
                    @if($referralLink ?? null)
                        <div class="mt-4 inline-flex items-center gap-2 bg-white/10 backdrop-blur rounded-full px-3 py-1 text-xs">
                            <span>👤</span>
                            <span>Diundang oleh: {{ $referralLink->name ?? $referralLink->slug }}</span>
                        </div>
                    @endif
                </div>

                @if(session('status'))
                    <div class="mx-8 mt-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-300 text-sm">
                        <div class="font-bold">✅ {{ session('status') }}</div>
                        <div class="text-xs mt-1 opacity-80">Tim kami akan segera menghubungi Anda.</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('lead-capture.store') }}" class="p-8 space-y-5">
                    @csrf
                    @if($referralLink ?? null)
                        <input type="hidden" name="referral_slug" value="{{ $referralLink->slug }}">
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1.5">Nama Lengkap *</label>
                        <input type="text" name="full_name" required value="{{ old('full_name') }}"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        @error('full_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1.5">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        @error('email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1.5">No. WhatsApp *</label>
                        <input type="tel" name="whatsapp" required value="{{ old('whatsapp') }}" placeholder="08xx xxxx xxxx"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        @error('whatsapp') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-1.5">No. Telepon</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                    </div>

                    <input type="hidden" name="source_channel" value="lead_capture_form">

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-extrabold text-lg rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                        Kirim Data Diri →
                    </button>

                    <div class="text-xs text-center text-gray-500 dark:text-gray-400 mt-2">
                        Dengan mengirimkan data, Anda setuju dengan kebijakan privasi kami.
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
