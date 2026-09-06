<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Follow-Up Automations (5-Step Sequence)</h2>
            <div class="space-x-2 flex">
                <a href="{{ route('admin.integrations.webhook-logs') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium">
                    📜 Webhook Logs
                </a>
                <form method="POST" action="{{ route('admin.followups.test-run') }}" class="inline">
                    @csrf
                    <input type="hidden" name="contact_id" value="1">
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium">
                        🧪 Test Run Sequence
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-green-100">
                    <p class="text-xs text-green-600 uppercase tracking-wide font-medium">Active Flows</p>
                    <p class="text-4xl font-bold text-green-700 mt-2">{{ $stats['active_flows'] }}<span class="text-lg font-medium text-green-500 ml-1">flows</span></p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-blue-100">
                    <p class="text-xs text-blue-600 uppercase tracking-wide font-medium">Triggered (Hari Ini)</p>
                    <p class="text-4xl font-bold text-blue-700 mt-2">{{ $stats['total_triggered_today'] }}</p>
                </div>
                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-amber-100">
                    <p class="text-xs text-amber-600 uppercase tracking-wide font-medium">Rata-rata Open Rate</p>
                    <p class="text-4xl font-bold text-amber-700 mt-2">{{ $stats['avg_open_rate'] }}<span class="text-lg font-medium text-amber-500 ml-1">%</span></p>
                </div>
                <div class="bg-gradient-to-br from-pink-50 to-rose-50 overflow-hidden shadow-sm sm:rounded-lg p-5 border border-pink-100">
                    <p class="text-xs text-pink-600 uppercase tracking-wide font-medium">Rata-rata Click Rate</p>
                    <p class="text-4xl font-bold text-pink-700 mt-2">{{ $stats['avg_click_rate'] }}<span class="text-lg font-medium text-pink-500 ml-1">%</span></p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 border">
                <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 px-6 py-4 border-b">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">5-Step Drip Follow-Up Sequence</h3>
                            <p class="text-sm text-gray-600 mt-1">Trigger: Saat lead baru dibuat (LeadCreated) — otomatis mengirim 5 email/WA bertahap.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-indigo-700">Jadwal Pengiriman</p>
                            <p class="text-sm text-gray-600">D+1 ➜ D+3 ➜ D+7 ➜ D+14 ➜ D+30</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    @foreach($sequenceDays as $day)
                        @php
                            $tpl = $defaultTemplates[$day];
                            $channels = [
                                'email' => ['bg-blue-500', 'Email'],
                                'whatsapp' => ['bg-green-500', 'WhatsApp'],
                                'email_whatsapp' => ['bg-purple-500', 'Email + WA'],
                                'sms' => ['bg-orange-500', 'SMS'],
                            ];
                            [$channelBg, $channelLabel] = $channels[$tpl['channel']] ?? ['bg-gray-500', $tpl['channel']];
                            $active = cache("followup_stage_{$day}_active") !== false && $tpl['active'];
                        @endphp
                        <div class="border rounded-xl p-5 transition hover:shadow-md {{ $active ? 'bg-white' : 'bg-gray-50 opacity-60' }}">
                            <div class="flex items-start justify-between gap-4 flex-wrap">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3 flex-wrap gap-y-2">
                                        <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br {{ $active ? 'from-indigo-500 to-purple-500 text-white' : 'from-gray-300 to-gray-400 text-white' }} font-bold text-xl shadow">
                                            D+{{ $day }}
                                        </div>
                                        <div>
                                            <h4 class="text-md font-bold text-gray-900">{{ $tpl['name'] }}</h4>
                                            <p class="text-xs text-gray-500 mt-1">🎯 {{ $tpl['goal'] }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold text-white {{ $channelBg }}">
                                                {{ $channelLabel }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                ⏱️ {{ $tpl['delay_hours'] }} jam setelah sign-up
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $active ? '✅ AKTIF' : '⛔ NON-AKTIF' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                                        <div class="bg-blue-50/50 rounded-lg p-4 border border-blue-100">
                                            <p class="text-xs font-semibold text-blue-700 mb-2">📧 Subjek Email:</p>
                                            <p class="text-sm font-medium text-gray-800">{{ $tpl['subject'] }}</p>
                                            <p class="text-xs text-gray-500 mt-2">🎯 Target Open Rate: <strong class="text-amber-700">{{ $tpl['open_rate_target'] }}%</strong></p>
                                        </div>
                                        <div class="bg-amber-50/50 rounded-lg p-4 border border-amber-100">
                                            <p class="text-xs font-semibold text-amber-700 mb-2">📝 Template Konten:</p>
                                            <pre class="text-xs text-gray-700 whitespace-pre-wrap bg-white p-3 rounded border border-amber-200 max-h-40 overflow-y-auto font-sans leading-relaxed">{{ $tpl['template_body'] }}</pre>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2">
                                    <form method="POST" action="{{ route('admin.followups.toggle-stage', $day) }}">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 rounded-lg text-xs font-semibold {{ $active ? 'bg-red-50 hover:bg-red-100 text-red-700 border border-red-200' : 'bg-green-50 hover:bg-green-100 text-green-700 border border-green-200' }}">
                                            {{ $active ? '🔴 Nonaktifkan' : '🟢 Aktifkan' }}
                                        </button>
                                    </form>
                                    <button type="button" onclick="document.getElementById('preview_modal_{{ $day }}').classList.remove('hidden')" class="px-4 py-2 rounded-lg text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200">
                                        👁️ Preview
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="preview_modal_{{ $day }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="this.classList.add('hidden')">
                            <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[85vh] overflow-hidden shadow-2xl" onclick="event.stopPropagation()">
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 px-6 py-4 flex justify-between items-center">
                                    <h3 class="text-lg font-bold text-white">Preview: {{ $tpl['name'] }}</h3>
                                    <button onclick="document.getElementById('preview_modal_{{ $day }}').classList.add('hidden')" class="text-white/80 hover:text-white text-2xl">&times;</button>
                                </div>
                                <div class="p-6 space-y-4 overflow-y-auto">
                                    <div class="bg-gray-50 border rounded-lg p-4">
                                        <p class="text-xs text-gray-500">Dikirim: <strong>{{ $tpl['delay_hours']/24 }} hari</strong> setelah lead dibuat</p>
                                        <p class="text-xs text-gray-500">Channel: <strong>{{ $channelLabel }}</strong></p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase">Subjek</label>
                                        <p class="text-lg font-medium text-gray-900 mt-1 p-3 bg-blue-50 border border-blue-100 rounded">{{ $tpl['subject'] }}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase">Body</label>
                                        <div class="mt-1 p-4 border rounded-lg bg-white whitespace-pre-wrap text-sm leading-relaxed text-gray-800 font-sans">
                                            {{ $tpl['template_body'] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="border-t px-6 py-3 flex justify-end bg-gray-50">
                                    <button onclick="document.getElementById('preview_modal_{{ $day }}').classList.add('hidden')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Tutup</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Recent Automation Flows</h3>
                    <span class="text-xs text-gray-500">10 terbaru</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Flow</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trigger</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Step</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Masuk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trigger Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($flows as $flow)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $flow->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $flow->trigger_type ?? 'Manual' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $flow->status === \App\Enums\AutomationFlowStatus::Active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ is_object($flow->status) ? strtoupper($flow->status->value) : strtoupper($flow->status ?? '-') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $flow->steps->count() }} step</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-700">{{ $flow->total_entered_count ?? 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $flow->last_triggered_at ? $flow->last_triggered_at->diffForHumans() : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">
                                    Belum ada automation flow. Flow otomatis follow-up menggunakan sequence template di atas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
