<div class="space-y-4">
    @if($appliedCoupon)
        <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
            <div class="flex items-center space-x-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded font-mono text-sm font-bold bg-indigo-100 text-indigo-800">
                            {{ $appliedCoupon['code'] }}
                        </span>
                        <span class="text-sm font-semibold text-green-700">
                            {{ $this->getDiscountLabel() }}
                        </span>
                    </div>
                    @if($appliedCoupon['min_order_total'] ?? 0 > 0)
                        <p class="text-xs text-gray-500 mt-0.5">
                            Berlaku untuk min. pesanan Rp {{ number_format($appliedCoupon['min_order_total'], 0, ',', '.') }}
                        </p>
                    @endif
                </div>
            </div>
            <button
                type="button"
                wire:click="remove"
                class="text-sm text-red-600 hover:text-red-800 font-medium px-3 py-1 rounded hover:bg-red-50 transition"
            >
                Hapus
            </button>
        </div>
    @endif

    <div class="flex space-x-2">
        <div class="flex-1 relative">
            <input
                type="text"
                wire:model="code"
                placeholder="Masukkan kode kupon..."
                class="w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:border-indigo-500 focus:ring focus:ring-indigo-200 uppercase tracking-wider"
                @if($appliedCoupon) disabled @endif
                wire:keydown.enter="apply"
            >
            @if($isLoading)
                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
            @endif
        </div>
        <button
            type="button"
            wire:click="apply"
            @if($appliedCoupon || $isLoading) disabled @endif
            class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed whitespace-nowrap"
        >
            Terapkan
        </button>
    </div>

    @if($message)
        @php
            $msgClass = match($messageType) {
                'success' => 'bg-green-50 text-green-800 border-green-200',
                'error' => 'bg-red-50 text-red-800 border-red-200',
                default => 'bg-blue-50 text-blue-800 border-blue-200',
            };
        @endphp
        <div class="px-4 py-2 rounded-lg border text-sm {{ $msgClass }}">
            {{ $message }}
        </div>
    @endif
</div>
