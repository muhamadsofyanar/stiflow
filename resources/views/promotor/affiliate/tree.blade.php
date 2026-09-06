<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Pohon Sponsor / Downline</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-3xl p-8 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-green-100 text-sm uppercase tracking-wider">ANDA (Level 0)</div>
                        <div class="font-extrabold text-2xl mt-1">{{ auth()->user()->name }}</div>
                        <div class="font-mono text-green-100">Kode: {{ auth()->user()->promoterProfile?->stifin_code ?? '-' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-black">{{ $directReferrals->count() ?? 0 }}</div>
                        <div class="text-green-100 text-sm">Direct Referral</div>
                    </div>
                </div>
            </div>

            @foreach($levels ?? [] as $depth => $levelReferrals)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 dark:text-white">Level {{ $depth }} ({{ $levelReferrals->count() }} orang)</h3>
                        <div class="text-xs text-gray-500">Jarak sponsor: {{ $depth }}</div>
                    </div>
                    <div class="p-6">
                        @if($levelReferrals->isEmpty())
                            <p class="text-gray-500 text-center py-4">Belum ada downline di level ini.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($levelReferrals as $ref)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ $ref->referredUser?->name ?? $ref->referredContact?->full_name ?? 'Anonim' }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            @if($ref->referredUser?->promoterProfile)
                                                <span class="font-mono text-green-600 font-bold">Promotor · {{ $ref->referredUser->promoterProfile->stifin_code }}</span>
                                            @elseif($ref->referredContact)
                                                <span class="text-amber-600">Lead / Calon Promotor</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400 mt-2">{{ $ref->created_at?->format('Y-m-d') }} · {{ $ref->status?->value ?? $ref->status }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
