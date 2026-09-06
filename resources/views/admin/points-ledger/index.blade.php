<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Points Ledger</h2></x-slot>
    <div class="py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <form method="POST" action="{{ route('admin.points-ledger.store') }}" class="bg-white dark:bg-gray-800 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-5 gap-4">@csrf
            <input name="user_id" type="number" min="1" required placeholder="User ID" class="rounded-lg border-gray-300 dark:bg-gray-800">
            <select name="direction" class="rounded-lg border-gray-300 dark:bg-gray-800"><option value="credit">Tambah</option><option value="debit">Kurangi</option></select>
            <input name="amount_points" type="number" min="1" required placeholder="Jumlah poin" class="rounded-lg border-gray-300 dark:bg-gray-800">
            <input name="notes" placeholder="Alasan" required class="rounded-lg border-gray-300 dark:bg-gray-800">
            <button class="bg-blue-600 text-white rounded-lg font-semibold">Simpan</button>
        </form>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden"><table class="w-full text-sm"><thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-6 py-3 text-left">Waktu</th><th class="px-6 py-3 text-left">Pengguna</th><th class="px-6 py-3 text-left">Arah</th><th class="px-6 py-3 text-left">Poin</th><th class="px-6 py-3 text-left">Saldo</th><th class="px-6 py-3 text-left">Alasan</th></tr></thead><tbody class="divide-y dark:divide-gray-700">
        @forelse($entries as $entry)<tr><td class="px-6 py-4">{{ $entry->created_at?->format('d-m-Y H:i') }}</td><td class="px-6 py-4">{{ $entry->user?->name }}</td><td class="px-6 py-4">{{ $entry->direction?->value ?? $entry->direction }}</td><td class="px-6 py-4">{{ number_format($entry->amount_points) }}</td><td class="px-6 py-4">{{ number_format($entry->balance_after_points ?? 0) }}</td><td class="px-6 py-4">{{ $entry->reason_text ?? '-' }}</td></tr>@empty<tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">Belum ada transaksi poin.</td></tr>@endforelse
        </tbody></table><div class="px-6 py-4">{{ $entries->links() }}</div></div>
    </div></div>
</x-app-layout>
