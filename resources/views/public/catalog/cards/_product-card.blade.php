<a href="{{ route('public.catalog.show', $product) }}" class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-0.5 transition-all">
    <div class="aspect-video bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
        @if($product->featured_image_path)
            <img src="{{ Storage::url($product->featured_image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        @else
            <span class="text-5xl opacity-30">📦</span>
        @endif
    </div>
    <div class="p-5">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-bold uppercase tracking-wide px-2 py-1 bg-indigo-50 text-indigo-700 rounded-full">{{ $product->type }}</span>
        </div>
        <h3 class="font-bold text-lg text-slate-900 group-hover:text-indigo-700 mb-2 line-clamp-2">{{ $product->name }}</h3>
        <p class="text-sm text-slate-500 line-clamp-2 mb-4 min-h-[2.5rem]">{{ $product->meta_description ?? $product->description }}</p>
        <div class="flex items-center justify-between">
            <p class="text-2xl font-black text-slate-900">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
            <span class="text-sm font-semibold text-indigo-600 group-hover:translate-x-1 transition-transform">Detail →</span>
        </div>
    </div>
</a>
