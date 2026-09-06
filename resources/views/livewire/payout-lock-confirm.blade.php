<div class="border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 rounded-xl p-5 space-y-4">
    <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-amber-200 dark:bg-amber-800 flex items-center justify-center text-xl">🔒</div>
        <div>
            <div class="font-bold text-amber-900 dark:text-amber-100">Kunci Payout Sebelum Approve</div>
            <div class="text-xs text-amber-700 dark:text-amber-300">Status: {{ $payout->status?->value ?? $payout->status }} · Total: Rp {{ number_format((float)($payout->total_amount ?? 0), 0, ',', '.') }}</div>
        </div>
    </div>

    @if(($payout->status?->value ?? $payout->status) !== 'draft')
        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3 text-sm text-gray-600 dark:text-gray-300">
            Payout sudah {{ $payout->status?->value ?? $payout->status }}. Kunci hanya berlaku saat status <code class="text-xs bg-gray-200 dark:bg-gray-800 px-1 rounded">draft</code>.
        </div>
    @else
        <div>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox"
                       wire:model="confirmLock"
                       class="mt-1 rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                <span class="text-sm text-amber-900 dark:text-amber-100">
                    <span class="font-bold">Saya menyetujui data payout ini.</span><br>
                    <span class="text-xs">Setelah dikunci, payout tidak dapat diedit lagi dan siap disetujui admin.</span>
                </span>
            </label>
        </div>

        <div>
            <label class="block text-xs font-semibold text-amber-800 dark:text-amber-200 mb-1">Catatan (opsional)</label>
            <textarea wire:model="notes"
                      rows="2"
                      class="w-full rounded-lg border-amber-300 dark:border-amber-700 dark:bg-gray-800 text-sm"
                      placeholder="Catatan verifikasi..."></textarea>
        </div>

        <button wire:click="confirmAndLock"
                wire:loading.attr="disabled"
                :disabled="!confirmLock"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition">
            <span wire:loading.remove>🔒 Kunci & Tandai Siap Approve</span>
            <span wire:loading>Mengunci...</span>
        </button>
    @endif
</div>
