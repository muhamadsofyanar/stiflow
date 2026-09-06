<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Landing Page — {{ $landingPage->title ?? 'Baru' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 min-h-screen">
<div class="min-h-screen">
    <div class="bg-slate-900 text-white py-3 px-6 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-emerald-400 flex items-center justify-center font-black">SF</div>
            <span class="font-bold">Admin / Landing Pages / {{ $landingPage->id ? 'Edit' : 'Baru' }}</span>
        </div>
        <a href="{{ route('admin.landing-pages.index') }}" class="text-sm text-slate-300 hover:text-white">← Daftar Landing Page</a>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ $landingPage->id ? route('admin.landing-pages.update', $landingPage) : route('admin.landing-pages.store') }}" class="bg-white border border-slate-200 rounded-2xl p-8 space-y-6">
            @csrf
            @if($landingPage->id) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Judul Landing Page</label>
                    <input type="text" name="title" value="{{ old('title', $landingPage->title) }}" required class="w-full rounded-xl border-slate-300 text-lg">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Slug (URL)</label>
                    <div class="flex rounded-xl border border-slate-300 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                        <span class="flex items-center px-3 bg-slate-50 border-r border-slate-300 text-sm text-slate-500 font-mono">/lp/</span>
                        <input type="text" name="slug" value="{{ old('slug', $landingPage->slug) }}" class="flex-1 border-0 focus:ring-0 font-mono">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Status</label>
                <select name="status" class="w-full rounded-xl border-slate-300">
                    <option value="Draft" @selected(old('status', $landingPage->status->value ?? 'Draft') === 'Draft')>Draft</option>
                    <option value="Published" @selected(old('status', $landingPage->status->value ?? '') === 'Published')>Published</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Blocks JSON (satu blok = judul + konten)</label>
                <p class="text-xs text-slate-500 mb-2">Format array blok: <code>[{ "type": "hero", "title": "...", "content": "..." }, ...]</code>. Tipe: hero, features, testimonial, pricing, faq, cta, text, image, video, leadform, countdown, social-proof, bullets, footer.</p>
                <textarea name="blocks_json" rows="16" class="w-full rounded-xl border-slate-300 font-mono text-sm" placeholder='[
  {"type":"hero","title":"Selamat Datang","subtitle":"Sub judul hero","button_text":"Mulai Sekarang","button_url":"#"},
  {"type":"features","title":"Fitur Unggulan","features":[{"icon":"✨","title":"Cepat","desc":"Lorem ipsum"}]}
]'>{{ old('blocks_json', json_encode($landingPage->blocks_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Meta JSON (SEO, OG tags, dll)</label>
                <textarea name="meta_json" rows="6" class="w-full rounded-xl border-slate-300 font-mono text-sm" placeholder='{ "description": "...", "og_image": "..." }'>{{ old('meta_json', json_encode($landingPage->meta_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</textarea>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-200">
                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow">
                    {{ $landingPage->id ? 'Simpan Perubahan' : 'Buat Landing Page' }}
                </button>
                <a href="{{ route('admin.landing-pages.index') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl">Batal</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
