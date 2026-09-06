@php($item = $variant ?? null)
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div><x-input-label for="product_id" value="Produk *"/><select id="product_id" name="product_id" required class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800">@foreach($products as $product)<option value="{{ $product->id }}" @selected((string) old('product_id', $selectedProductId) === (string) $product->id)>{{ $product->name }}</option>@endforeach</select></div>
    <div><x-input-label for="name" value="Nama varian *"/><x-text-input id="name" name="name" required class="mt-1 w-full" :value="old('name', $item?->name)"/></div>
    <div><x-input-label for="sku" value="SKU *"/><x-text-input id="sku" name="sku" required class="mt-1 w-full" :value="old('sku', $item?->sku)"/></div>
    <div><x-input-label for="price_override" value="Harga khusus"/><x-text-input id="price_override" name="price_override" type="number" min="0" class="mt-1 w-full" :value="old('price_override', $item?->price_override)"/></div>
    <div><x-input-label for="stock_qty" value="Jumlah stok"/><x-text-input id="stock_qty" name="stock_qty" type="number" min="0" class="mt-1 w-full" :value="old('stock_qty', $item?->stock_qty)"/></div>
    <div><x-input-label for="weight_gram" value="Berat (gram)"/><x-text-input id="weight_gram" name="weight_gram" type="number" min="0" class="mt-1 w-full" :value="old('weight_gram', $item?->weight_gram)"/></div>
    <div><x-input-label for="sort_order" value="Urutan"/><x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 w-full" :value="old('sort_order', $item?->sort_order ?? 0)"/></div>
    <div><x-input-label for="license_activation_limit" value="Batas aktivasi lisensi"/><x-text-input id="license_activation_limit" name="license_activation_limit" class="mt-1 w-full" :value="old('license_activation_limit', $item?->license_activation_limit)"/></div>
    <div><x-input-label for="license_expiry_days" value="Masa lisensi (hari)"/><x-text-input id="license_expiry_days" name="license_expiry_days" class="mt-1 w-full" :value="old('license_expiry_days', $item?->license_expiry_days)"/></div>
    <div class="md:col-span-2"><x-input-label for="attributes_json" value="Atribut JSON"/><textarea id="attributes_json" name="attributes_json" rows="3" class="mt-1 w-full font-mono rounded-lg border-gray-300 dark:bg-gray-800">{{ old('attributes_json', $item?->attributes_json ? json_encode($item->attributes_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div>
    <div class="md:col-span-2 flex gap-6"><label class="inline-flex items-center gap-2"><input type="checkbox" name="in_stock" value="1" @checked(old('in_stock', $item?->in_stock ?? true))>Tersedia</label><label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item?->is_active ?? true))>Aktif</label></div>
</div>
<x-input-error :messages="$errors->all()" class="mt-4"/>
