<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Kelola Promotor</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('admin.promotors.index') }}" class="mb-5 grid grid-cols-1 md:grid-cols-4 gap-3">
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama/email/kode STIFIN" class="md:col-span-2 rounded-lg border px-3 py-2 dark:bg-gray-800 dark:text-white dark:border-gray-700" />
                <select name="status" class="rounded-lg border px-3 py-2 dark:bg-gray-800 dark:text-white dark:border-gray-700">
                    <option value="">Semua Status</option>
                    @foreach(App\Enums\PromoterVerificationStatus::cases() as $s)
                        <option value="{{ $s->value }}" @selected($status == $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900 flex-1">Cari</button>
                    <a href="{{ route('admin.promotors.index') }}" class="px-4 py-2 rounded-lg border text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50">Reset</a>
                </div>
            </form>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Promotor</th>
                                <th class="px-6 py-3 text-left font-medium">Kode STIFIN</th>
                                <th class="px-6 py-3 text-center font-medium">Status</th>
                                <th class="px-6 py-3 text-left font-medium">Terverifikasi</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($profiles as $p)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $p->user?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $p->user?->email ?? '-' }} · {{ $p->user?->phone ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-3 font-mono font-bold text-gray-900 dark:text-white">{{ $p->stifin_code }}</td>
                                    <td class="px-6 py-3 text-center">
                                        <x-status-badge :value="$p->verification_status" />
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 dark:text-gray-300 text-xs">
                                        {{ $p->verified_at ? $p->verified_at : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        @if(!$p->isVerified() && auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('admin.promotors.verify', $p) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs rounded-md bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1.5">
                                                    ✓ Verifikasi
                                                </button>
                                            </form>
                                        @elseif($p->isVerified())
                                            <span class="text-xs text-gray-400">✓ Verified</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">{{ $profiles->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
