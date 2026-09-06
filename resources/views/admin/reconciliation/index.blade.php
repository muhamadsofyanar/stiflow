<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Rekonsiliasi Order (Kasus Perlu Review)</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-5 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg px-4 py-3 text-green-800 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif
            <div class="mb-5">
                <a href="{{ route('admin.reconciliation.index', ['status' => 'open']) }}"
                   class="mr-2 px-3 py-1.5 rounded-md text-sm font-semibold {{ $status == 'open' ? 'bg-orange-600 text-white' : 'bg-white dark:bg-gray-800 border text-gray-700 dark:text-gray-200' }}">
                    Perlu Review (Open)
                </a>
                <a href="{{ route('admin.reconciliation.index', ['status' => 'resolved']) }}"
                   class="px-3 py-1.5 rounded-md text-sm font-semibold {{ $status == 'resolved' ? 'bg-green-600 text-white' : 'bg-white dark:bg-gray-800 border text-gray-700 dark:text-gray-200' }}">
                    Selesai (Resolved)
                </a>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">#ID</th>
                                <th class="px-6 py-3 text-left font-medium">Subjek</th>
                                <th class="px-6 py-3 text-left font-medium">Alasan</th>
                                <th class="px-6 py-3 text-center font-medium">Status</th>
                                <th class="px-6 py-3 text-left font-medium">Dibuat</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($cases as $c)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 align-top">
                                    <td class="px-6 py-4 font-mono text-gray-700 dark:text-gray-300">#{{ $c->id }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ class_basename($c->subject_type) }} #{{ $c->subject_id }}
                                        </div>
                                        @php $evidence = $c->evidence_json ?? []; @endphp
                                        @if($c->status == 'open')
                                            <details class="mt-2 max-w-sm">
                                                <summary class="text-xs font-semibold text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">Lihat Bukti & Referensi</summary>
                                                <pre class="text-xs mt-2 bg-gray-100 dark:bg-gray-700 rounded p-2 overflow-auto max-h-48">{{ json_encode($evidence, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                                            </details>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300 max-w-sm">{{ $c->reason }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <x-status-badge :value="$c->status" />
                                        @if($c->resolution)
                                            <div class="mt-1 text-xs font-semibold text-gray-500">{{ $c->resolution->value }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">{{ $c->created_at }}<br/>{{ $c->resolved_at ? 'Selesai: ' . $c->resolved_at : '' }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @if($c->status == 'open' && auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('admin.reconciliation.resolve', $c) }}" class="space-y-2">
                                                @csrf
                                                <select name="resolution" class="w-full rounded border text-xs px-2 py-1.5 dark:bg-gray-900 dark:text-white dark:border-gray-700">
                                                    <option value="manual_success">✓ Manual: Voucher berhasil terkirim</option>
                                                    <option value="manual_rollback">✗ Manual: Voucher gagal (reject/refund)</option>
                                                    <option value="no_action">– Tidak ada aksi</option>
                                                </select>
                                                <input type="text" name="note" placeholder="Catatan..." class="w-full rounded border text-xs px-2 py-1.5 dark:bg-gray-900 dark:text-white dark:border-gray-700" />
                                                <button type="submit" class="w-full text-xs rounded-md bg-blue-600 hover:bg-blue-700 text-white font-semibold px-3 py-1.5">
                                                    Simpan Resolusi
                                                </button>
                                            </form>
                                        @elseif($c->status == 'resolved')
                                            <span class="text-xs text-gray-400">{{ $c->resolution_note ?? '' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if($cases->isEmpty())
                                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Tidak ada kasus.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">{{ $cases->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
