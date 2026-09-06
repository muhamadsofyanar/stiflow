<div class="space-y-4 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
    <div class="flex items-center gap-3 mb-2">
        <div class="h-10 w-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center text-xl">📨</div>
        <div>
            <div class="font-bold text-gray-900 dark:text-white">Jadwalkan & Kirim Kampanye</div>
            <div class="text-xs text-gray-500">{{ $campaign->name }} · Channel: {{ $campaign->channel }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Tanggal & Jam Kirim</label>
            <input type="datetime-local"
                   wire:model="scheduledAt"
                   min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
            @error('scheduledAt') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Test Email (untuk kirim percobaan)</label>
            <input type="email"
                   wire:model="testRecipient"
                   placeholder="test@contoh.com"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
            @error('testRecipient') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-2 pt-2">
        <button wire:click="schedule"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg disabled:opacity-50">
            <span>📅 Jadwalkan Kirim</span>
            <span wire:loading>Sedang menjadwalkan...</span>
        </button>
        <button wire:click="sendTest"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg disabled:opacity-50">
            <span>🧪 Kirim Test Email</span>
            <span wire:loading>Mengirim test...</span>
        </button>
    </div>

    @if(session('status'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-3 text-green-700 dark:text-green-300 text-sm">
            {{ session('status') }}
        </div>
    @endif
</div>
