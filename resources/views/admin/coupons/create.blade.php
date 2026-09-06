<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $coupon->exists ? 'Edit Kupon: ' . $coupon->code : 'Buat Kupon Baru' }}
            </h2>
            <a href="{{ route('admin.coupons.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Kembali ke Daftar Kupon</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
                    @csrf
                    @if($coupon->exists)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Kupon *</label>
                            <input type="text" name="code" value="{{ old('code', strtoupper($coupon->code)) }}" required maxlength="50"
                                class="w-full rounded-md border-gray-300 border px-3 py-2 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <p class="text-xs text-gray-500 mt-1">Gunakan huruf besar & tanpa spasi (contoh: RAMADHAN2025)</p>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Diskon *</label>
                            <select name="type" required class="w-full rounded-md border-gray-300 border px-3 py-2">
                                <option value="fixed" {{ old('type', $coupon->type) === 'fixed' ? 'selected' : '' }}>Fixed (Nominal)</option>
                                <option value="percent" {{ old('type', $coupon->type) === 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                            </select>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Diskon *</label>
                            <input type="number" name="value" value="{{ old('value', $coupon->value) }}" required min="0" step="any"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Jika persentase, masukkan angka 5-100</p>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Diskon (Hanya untuk %)</label>
                            <input type="number" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}" min="0" step="any"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min. Total Order</label>
                            <input type="number" name="min_order_total" value="{{ old('min_order_total', $coupon->min_order_total) }}" min="0" step="any"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Pemakaian Global</label>
                            <input type="number" name="max_redemptions_global" value="{{ old('max_redemptions_global', $coupon->max_redemptions_global) }}" min="1"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Kosongkan untuk unlimited</p>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max per User</label>
                            <input type="number" name="max_redemptions_per_user" value="{{ old('max_redemptions_per_user', $coupon->max_redemptions_per_user) }}" min="1"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                        </div>

                        @if($coupon->exists)
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" required class="w-full rounded-md border-gray-300 border px-3 py-2">
                                <option value="active" {{ old('status', $coupon->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $coupon->status) === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                <option value="expired" {{ old('status', $coupon->status) === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                            </select>
                        </div>
                        @endif

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Mulai</label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai</label>
                            <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" rows="2"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">{{ old('description', $coupon->description) }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
                        <a href="{{ route('admin.coupons.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-md font-medium">
                            Batal
                        </a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium">
                            {{ $coupon->exists ? 'Simpan Perubahan' : 'Buat Kupon' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
