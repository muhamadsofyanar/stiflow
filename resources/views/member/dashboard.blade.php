<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Member Dashboard</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 text-white rounded-3xl p-8 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-blue-100 text-sm">Selamat datang kembali,</div>
                        <div class="font-extrabold text-3xl mt-1">{{ auth()->user()->name }}</div>
                        <div class="text-blue-100 mt-2">Kelola pesanan, kelas, lisensi, dan lainnya dari sini.</div>
                    </div>
                    <div class="hidden md:block h-20 w-20 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center text-4xl font-black">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('member.orders.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-xl">🧾</div>
                        <div class="text-right flex-1">
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Pesanan</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $cardData['orders_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-blue-600 group-hover:underline">Lihat pesanan →</div>
                </a>

                <a href="{{ route('member.kelas.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center text-xl">📚</div>
                        <div class="text-right flex-1">
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Kelas</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $cardData['kelas_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-green-600 group-hover:underline">Buka kelas →</div>
                </a>

                <a href="{{ route('member.hasil-stifin.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center text-xl">🧬</div>
                        <div class="text-right flex-1">
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Hasil STIFIN</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $cardData['stifin_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-purple-600 group-hover:underline">Lihat hasil →</div>
                </a>

                <a href="{{ route('member.unduhan.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-xl">⬇️</div>
                        <div class="text-right flex-1">
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Unduhan</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $cardData['unduhan_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-indigo-600 group-hover:underline">Unduh berkas →</div>
                </a>

                <a href="{{ route('member.lisensi.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-xl">🔑</div>
                        <div class="text-right flex-1">
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Lisensi</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $cardData['lisensi_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-amber-600 group-hover:underline">Kelola lisensi →</div>
                </a>

                <a href="{{ route('member.poin.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-pink-100 dark:bg-pink-900/40 flex items-center justify-center text-xl">⭐</div>
                        <div class="text-right flex-1">
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Poin</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($cardData['poin_balance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-pink-600 group-hover:underline">Riwayat poin →</div>
                </a>

                <div class="group bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center text-xl">🎁</div>
                        <div class="text-right flex-1">
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Promo</div>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">{{ $cardData['promo_count'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-yellow-600">Kupon aktif</div>
                </div>

                <a href="{{ route('member.profil.index') }}" class="group bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-900/40 flex items-center justify-center text-xl">👤</div>
                        <div class="text-right flex-1">
                            <div class="text-xs text-gray-500 uppercase tracking-wider">Profil</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ $cardData['profil']?->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-semibold text-slate-600 group-hover:underline">Ubah profil →</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
