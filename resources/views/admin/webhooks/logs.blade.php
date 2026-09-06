<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Webhook Event Logs</h2>
            <a href="{{ route('admin.automations.follow-ups') }}" class="bg-purple-100 text-purple-700 px-4 py-2 rounded-lg hover:bg-purple-200 text-sm font-medium">
                ⚙️ Follow-Up Automations
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center flex-wrap gap-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">50 Webhook Event Terakhir</h3>
                        <p class="text-xs text-gray-500 mt-1">Log event dari semua provider: WhatsApp, Email, Payment Gateway.</p>
                    </div>
                    <div class="flex space-x-3">
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            <span class="text-gray-600">Signature Valid</span>
                        </div>
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                            <span class="text-gray-600">Invalid / Error</span>
                        </div>
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                            <span class="text-gray-600">Belum diproses</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Signature</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outcome</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">HTTP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @php
                                $logs = \App\Models\ProviderWebhookEvent::query()
                                    ->latest('id')
                                    ->limit(50)
                                    ->get();
                            @endphp
                            @forelse($logs as $idx => $log)
                                @php
                                    $sigColor = $log->signature_valid === true ? 'bg-green-100 text-green-700' : ($log->signature_valid === false ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600');
                                    $sigLabel = $log->signature_valid === true ? '✓ VALID' : ($log->signature_valid === false ? '✗ INVALID' : '-');
                                    $outcomeClass = match(strtolower($log->outcome ?? 'received')) {
                                        'received', 'success', 'processed', 'ok' => 'bg-green-100 text-green-700',
                                        'failed', 'error', 'rejected' => 'bg-red-100 text-red-700',
                                        'signature_invalid', 'invalid' => 'bg-orange-100 text-orange-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                    $providerBadge = match(true) {
                                        str_contains(strtolower($log->provider_type), 'onesender') => 'bg-green-500 text-white',
                                        str_contains(strtolower($log->provider_type), 'starsender') => 'bg-yellow-500 text-white',
                                        str_contains(strtolower($log->provider_type), 'email_') => 'bg-blue-500 text-white',
                                        str_contains(strtolower($log->provider_type), 'payment_') => 'bg-purple-500 text-white',
                                        str_contains(strtolower($log->provider_type), 'wa') || str_contains(strtolower($log->provider_type), 'whatsapp') => 'bg-emerald-500 text-white',
                                        default => 'bg-gray-500 text-white',
                                    };
                                @endphp
                                <tr class="hover:bg-indigo-50/30 transition">
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-400 font-mono">
                                        {{ $logs->count() - $idx }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-800">{{ $log->created_at->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $providerBadge }}">
                                            {{ strtoupper(str_replace(['payment_', 'email_'], ['', '📧'], $log->provider_type)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 max-w-[160px] truncate" title="{{ $log->event_type }}">
                                            {{ $log->event_type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-mono text-xs text-gray-600 max-w-[180px] truncate" title="{{ $log->event_id }}">
                                            {{ Str::limit($log->event_id, 28) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $sigColor }}">
                                            {{ $sigLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $outcomeClass }}">
                                            {{ strtoupper($log->outcome ?? 'RECEIVED') }}
                                        </span>
                                        @if($log->outcome_message)
                                            <div class="text-xs text-red-500 mt-1 max-w-[140px] truncate" title="{{ $log->outcome_message }}">
                                                {{ $log->outcome_message }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs text-gray-500">
                                        {{ $log->request_ip ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                                            {{ strtolower($log->outcome ?? 'received') === 'received' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ strtolower($log->outcome ?? 'received') === 'received' ? '200 OK' : '500 ERR' }}
                                        </span>
                                    </td>
                                </tr>
                                @if($loop->iteration % 10 === 0 && ! $loop->last)
                                    <tr class="bg-gray-50 h-2">
                                        <td colspan="9"></td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 font-medium">Belum ada webhook event yang diterima.</p>
                                        <p class="text-xs text-gray-400 mt-1">Event dari WhatsApp, Email, & Payment gateway akan muncul di sini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->count() > 0)
                    <div class="px-6 py-4 border-t bg-gray-50 flex justify-between items-center text-xs text-gray-500">
                        <span>Menampilkan {{ $logs->count() }} event terbaru</span>
                        <span>Database ProviderWebhookEvent</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
