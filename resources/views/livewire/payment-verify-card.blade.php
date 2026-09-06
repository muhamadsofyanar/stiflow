<section class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-6 shadow-sm">
    <header class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg">Verifikasi Bukti Pembayaran</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Penerima: <span class="font-semibold">{{ $proof->sender_name ?? '-' }}</span>
                @ Bank {{ $proof->sender_bank ?? '-' }}
                · Rp {{ number_format((float) $proof->transfer_amount, 0, ',', '.') }}
                @if($proof->transfer_time)
                    · {{ $proof->transfer_time }}
                @endif
            </p>
        </div>
        <x-status-badge :value="$proof->review_status" />
    </header>

    <div class="mb-6 rounded-lg bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 p-2 inline-block">
        @if(str_starts_with($proof->mime_type ?? '', 'image/'))
            <img src="{{ route('payment-proofs.download', $proof) }}" alt="bukti" class="max-h-80 rounded" />
        @else
            <a href="{{ route('payment-proofs.download', $proof) }}" class="px-4 py-2 block text-blue-700 underline">Unduh bukti pembayaran</a>
        @endif
    </div>

    @if(!$proof->isApproved() && !$proof->isRejected())
        <div class="grid md:grid-cols-2 gap-6">
            <form method="POST" wire:submit.prevent="approve" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Catatan (opsional)</label>
                    <input type="text" wire:model="note" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-900 dark:text-white dark:border-gray-700" placeholder="Cocok dengan jumlah order" />
                </div>
                <button type="submit" class="w-full rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5">
                    ✓ Setujui Pembayaran
                </button>
            </form>
            <form method="POST" wire:submit.prevent="reject" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Alasan penolakan *</label>
                    <textarea wire:model="reason" required rows="3" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-900 dark:text-white dark:border-gray-700" placeholder="Jumlah kurang / bukti tidak jelas / dll."></textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5">
                    ✗ Tolak Pembayaran
                </button>
            </form>
        </div>
    @else
        <div class="rounded-lg border px-4 py-3 bg-gray-50 dark:bg-gray-700/50 text-sm">
            <div class="text-gray-600 dark:text-gray-300">
                Ditinjau oleh:
                <b class="text-gray-900 dark:text-white">{{ $proof->reviewer?->name ?? '-' }}</b>
                @if($proof->reviewed_at)
                    · {{ $proof->reviewed_at }}
                @endif
            </div>
            @if($proof->review_note)
                <div class="text-gray-700 dark:text-gray-300 mt-1">Catatan: <i>{{ $proof->review_note }}</i></div>
            @endif
        </div>
    @endif
</section>
