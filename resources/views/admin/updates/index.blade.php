<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Channel — Admin {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin / Update Channel</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-300 hover:text-white">← Dashboard</a>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('status') }}</div>
        @endif

        <div class="grid md:grid-cols-3 gap-4 mb-8">
            <div class="md:col-span-2 bg-gradient-to-r from-indigo-600 to-emerald-600 text-white rounded-2xl p-6 shadow-xl">
                <h1 class="text-2xl font-black mb-2">🚀 Update Channel</h1>
                <p class="text-indigo-100 mb-4">Periksa dan kelola rilis update aplikasi STIFLOW.</p>
                <div class="flex gap-3 items-center flex-wrap">
                    <div>
                        <p class="text-xs text-indigo-200 uppercase tracking-wide">Current Version</p>
                        <p class="text-lg font-black font-mono">v{{ $currentVersion }}</p>
                    </div>
                    @if($latestRelease)
                        <div class="h-10 w-px bg-white/20"></div>
                        <div>
                            <p class="text-xs text-indigo-200 uppercase tracking-wide">Latest {{ $latestRelease->channel->value }}</p>
                            <p class="text-lg font-black font-mono">v{{ $latestRelease->version_semver }}</p>
                        </div>
                        @if($latestRelease->version_semver !== $currentVersion)
                            <span class="px-3 py-1.5 bg-amber-400 text-amber-950 rounded-full text-xs font-black uppercase animate-pulse">UPDATE AVAILABLE</span>
                        @endif
                    @endif
                    <form method="POST" action="{{ route('admin.updates.check-now') }}" class="ml-auto">
                        @csrf
                        <button class="px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur text-white font-semibold rounded-xl border border-white/30 transition">
                            🔄 Check Now
                        </button>
                    </form>
                </div>
            </div>
            <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Channel Filter</p>
                <div class="flex gap-2 flex-wrap">
                    @foreach(['Stable', 'Preview', 'All'] as $c)
                        <a href="?channel={{ $c }}" class="px-3 py-1.5 rounded-lg text-sm font-semibold {{ $channel === $c ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $c }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold uppercase tracking-wide text-slate-500 text-xs">Version</th>
                        <th class="px-6 py-3 text-left font-bold uppercase tracking-wide text-slate-500 text-xs">Channel</th>
                        <th class="px-6 py-3 text-left font-bold uppercase tracking-wide text-slate-500 text-xs">Published</th>
                        <th class="px-6 py-3 text-left font-bold uppercase tracking-wide text-slate-500 text-xs">Size</th>
                        <th class="px-6 py-3 text-left font-bold uppercase tracking-wide text-slate-500 text-xs">Critical</th>
                        <th class="px-6 py-3 text-left font-bold uppercase tracking-wide text-slate-500 text-xs">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($releases as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <p class="font-mono font-black text-slate-900">v{{ $r->version_semver }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $r->channel->value === 'Stable' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $r->channel->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $r->published_at?->format('d M Y') ?? '—' }}</td>
                            <td class="px-6 py-4 font-mono text-slate-600">{{ $r->file_size_mb }} MB</td>
                            <td class="px-6 py-4">
                                @if($r->is_critical)
                                    <span class="px-2 py-1 bg-rose-50 text-rose-700 rounded-full text-xs font-bold">🔥 CRITICAL</span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 max-w-sm">
                                <p class="line-clamp-2 whitespace-pre-line">{{ Str::limit($r->release_notes_markdown, 120) }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($releases->count() === 0)
                <div class="p-12 text-center text-slate-500">Belum ada catatan rilis. Jalankan "Check Now" untuk sinkronisasi.</div>
            @endif
        </div>

        <div class="mt-6">{{ $releases->appends(['channel' => $channel])->links() }}</div>
    </div>
</div>
</body>
</html>
