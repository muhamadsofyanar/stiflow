<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Landing Pages — Admin {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin / Landing Pages</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-300 hover:text-white">← Dashboard</a>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Landing Pages</h1>
                <p class="text-slate-600">Kelola halaman landing page untuk lead capture dan campaign.</p>
            </div>
            <a href="{{ route('admin.landing-pages.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow">+ Landing Page Baru</a>
        </div>

        @if(session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('status') }}</div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Slug / URL</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Visits</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Published</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($landingPages as $lp)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-900">{{ $lp->title }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-indigo-600">/lp/{{ $lp->slug }}</td>
                            <td class="px-6 py-4">
                                @if($lp->isPublished())
                                    <span class="text-xs font-bold px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full">Published</span>
                                @else
                                    <span class="text-xs font-bold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-900">{{ number_format($lp->visits_count) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $lp->published_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="px-6 py-4 text-right text-sm space-x-2">
                                @if($lp->isPublished())
                                    <a href="{{ route('public.landing.show', $lp) }}" target="_blank" class="text-emerald-600 hover:text-emerald-800 font-semibold">View</a>
                                @endif
                                <a href="{{ route('admin.landing-pages.edit', $lp) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($landingPages->count() === 0)
                <div class="p-12 text-center text-slate-500">Belum ada landing page. Buat yang pertama!</div>
            @endif
        </div>

        <div class="mt-6">{{ $landingPages->links() }}</div>
    </div>
</div>
</body>
</html>
