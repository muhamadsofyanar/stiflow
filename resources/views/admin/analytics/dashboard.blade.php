<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Analytics Dashboard — Admin {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin / Analytics Dashboard</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-300 hover:text-white">← Dashboard</a>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Analytics Dashboard</h1>
                <p class="text-slate-600">Periode: <strong>{{ $periodLabel }}</strong> ({{ $days }} hari terakhir)</p>
            </div>
            <div class="flex gap-2">
                @foreach([7, 14, 30, 90] as $d)
                    <a href="?days={{ $d }}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ $days === $d ? 'bg-indigo-600 text-white shadow' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                        {{ $d }}H
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">💰 Gross Revenue</p>
                <p class="text-2xl font-black text-slate-900">Rp{{ number_format($cards['gross_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">💵 Net Revenue</p>
                <p class="text-2xl font-black text-emerald-700">Rp{{ number_format($cards['net_revenue'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">📦 Total Orders</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($cards['orders_total_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">✅ Orders Paid</p>
                <p class="text-2xl font-black text-emerald-600">{{ number_format($cards['orders_paid_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">⏳ Orders Pending</p>
                <p class="text-2xl font-black text-amber-600">{{ number_format($cards['orders_pending_count']) }}</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">🔍 Needs Review</p>
                <p class="text-2xl font-black text-rose-600">{{ number_format($cards['orders_needs_review_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">👥 New Contacts</p>
                <p class="text-2xl font-black text-indigo-600">{{ number_format($cards['new_contacts_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">⭐ Promotor Verified</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($cards['promoter_verified_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">🎟️ Voucher Unit</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($cards['voucher_units_sold']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">📊 AOV</p>
                <p class="text-2xl font-black text-slate-900">Rp{{ number_format($cards['average_order_value'], 0, ',', '.') }}</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">🎓 Enrollments</p>
                <p class="text-2xl font-black text-indigo-700">{{ number_format($cards['enrollments_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">📚 Lessons Done</p>
                <p class="text-2xl font-black text-emerald-700">{{ number_format($cards['lessons_completed_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">👁️ Unique Visitors</p>
                <p class="text-2xl font-black text-slate-900">{{ number_format($cards['unique_visitors_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">🛒 Purchases</p>
                <p class="text-2xl font-black text-emerald-600">{{ number_format($cards['purchases_count']) }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">💸 Commission Paid</p>
                <p class="text-2xl font-black text-rose-700">Rp{{ number_format($cards['commission_paid_amount'], 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="font-bold text-slate-900">📈 Daily Snapshots</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold text-slate-600">Tanggal</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-600">Gross</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-600">Orders</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-600">Paid</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-600">Leads</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-600">Visitors</th>
                            <th class="px-4 py-3 text-right font-bold text-slate-600">Pageviews</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($snapshots->reverse() as $s)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $s->snapshot_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right font-mono">Rp{{ number_format($s->gross_revenue, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($s->orders_total_count) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-700 font-semibold">{{ number_format($s->orders_paid_count) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($s->leads_count) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($s->unique_visitors_count) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($s->pageviews_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
