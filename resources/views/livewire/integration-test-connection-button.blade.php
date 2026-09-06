<div class="inline-flex items-center gap-2">
    <button wire:click="test"
            wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 px-3 py-2 bg-slate-600 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg disabled:opacity-50">
        <span wire:loading.remove>🔌 Test Koneksi</span>
        <span wire:loading class="inline-flex items-center gap-1">
            <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Testing...
        </span>
    </button>

    @if($lastResult !== null)
        <span class="text-xs inline-flex items-center gap-1 px-2 py-1 rounded-lg {{ $lastSuccess ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }}">
            <span>{{ $lastSuccess ? '✓' : '✗' }}</span>
            <span class="max-w-[300px] truncate">{{ $lastResult }}</span>
        </span>
    @endif
</div>
