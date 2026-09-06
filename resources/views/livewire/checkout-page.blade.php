<section class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Checkout Voucher STIFIN</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Voucher akan ditambahkan otomatis ke akun promotor:
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $this->product?->name ?? 'Voucher STIFIN' }}</span>
            </p>
        </div>
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/30 px-4 py-3 text-sm">
            <div class="text-blue-700 dark:text-blue-300">Kode Tujuan (tidak dapat diubah):</div>
            <div class="font-mono font-bold text-blue-800 dark:text-blue-200 mt-0.5">
                {{ auth()->user()->promoterProfile?->stifin_code ?? '-' }}
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kuantitas (unit)</label>
                <div class="flex gap-2 flex-wrap mb-3">
                    @foreach($this->presets as $p)
                        <button type="button" wire:click="setQty({{ $p }})"
                            class="px-3 py-1.5 rounded-lg text-sm font-semibold border transition
                                {{ $qty === (int)$p
                                    ? 'bg-blue-600 border-blue-700 text-white'
                                    : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:border-blue-300' }}">
                            {{ $p }} Voucher
                        </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="setQty({{ $qty - 1 }})" class="w-10 h-10 rounded-lg border text-lg font-bold bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200">−</button>
                    <input type="number" wire:model.live="qty" min="1" max="500"
                        class="w-32 text-center border rounded-lg px-3 py-2 dark:bg-gray-900 dark:text-white dark:border-gray-700" />
                    <button type="button" wire:click="setQty({{ $qty + 1 }})" class="w-10 h-10 rounded-lg border text-lg font-bold bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200">+</button>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                <div class="flex justify-between px-4 py-2.5 text-gray-600 dark:text-gray-300">
                    <span>Harga per voucher</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($this->unitPrice, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between px-4 py-2.5 text-gray-600 dark:text-gray-300">
                    <span>Jumlah voucher</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $qty }} unit</span>
                </div>
                <div class="flex justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/50 rounded-b-xl">
                    <span class="font-semibold text-gray-800 dark:text-gray-200">TOTAL BAYAR</span>
                    <span class="text-lg font-extrabold text-blue-700 dark:text-blue-400">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">Cara Pembayaran:</h3>
            <div class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 p-4 space-y-3 text-sm bg-gray-50 dark:bg-gray-700/30">
                <div>
                    <div class="text-gray-500 dark:text-gray-400">Transfer ke rekening:</div>
                    <div class="font-bold text-lg text-gray-900 dark:text-white">{{ $branch?->bank_name ?? '- Bank -' }}</div>
                    <div class="font-mono text-2xl font-extrabold tracking-wide text-gray-900 dark:text-white">{{ $branch?->bank_account ?? '- - -' }}</div>
                    <div class="text-gray-700 dark:text-gray-300">a/n <span class="font-semibold">{{ $branch?->bank_account_name ?? '- - -' }}</span></div>
                </div>
                <ol class="list-decimal list-inside space-y-1.5 text-gray-600 dark:text-gray-300 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <li>Jumlah transfer persis <b>Rp {{ number_format($this->total, 0, ',', '.') }}</b>.</li>
                    <li>Pilih menu Transfer/Bayar sesuai bank Anda.</li>
                    <li>Simpan screenshot bukti transfer.</li>
                    <li>Klik "Buat Order & Upload Bukti", lalu unggah screenshot.</li>
                </ol>
            </div>

            <form method="POST" action="{{ route('promotor.checkout.place-order') }}">
                @csrf
                <input type="hidden" name="qty" value="{{ $qty }}" />
                <button type="submit"
                    class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 shadow-sm transition">
                    Buat Order & Upload Bukti →
                </button>
            </form>
        </div>
    </div>
</section>
