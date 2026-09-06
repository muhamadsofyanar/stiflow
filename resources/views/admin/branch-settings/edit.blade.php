<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Pengaturan Cabang</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-5 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg px-4 py-3 text-green-800 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif
            <form method="POST" action="{{ route('admin.branch-settings.update') }}" class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                @csrf
                @method('PUT')
                <div class="grid md:grid-cols-2 gap-5 text-sm">
                    <div class="md:col-span-2">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">Identitas Cabang</h3>
                    </div>
                    <div>
                        <x-input-label for="branch_code" value="Kode Cabang *" />
                        <x-text-input id="branch_code" name="branch_code" class="mt-1 block w-full font-mono" :value="old('branch_code', $setting->branch_code)" required />
                        <x-input-error :messages="$errors->get('branch_code')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="brand_name" value="Nama Brand *" />
                        <x-text-input id="brand_name" name="brand_name" class="mt-1 block w-full" :value="old('brand_name', $setting->brand_name)" required />
                        <x-input-error :messages="$errors->get('brand_name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="contact" value="Kontak WA/Telp" />
                        <x-text-input id="contact" name="contact" class="mt-1 block w-full" :value="old('contact', $setting->contact)" />
                    </div>
                    <div>
                        <x-input-label for="locale" value="Locale" />
                        <x-text-input id="locale" name="locale" class="mt-1 block w-full" :value="old('locale', $setting->locale)" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="address" value="Alamat" />
                        <textarea id="address" name="address" rows="2" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm">{{ old('address', $setting->address) }}</textarea>
                    </div>

                    <div class="md:col-span-2 mt-4">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">Rekening Pembayaran</h3>
                    </div>
                    <div>
                        <x-input-label for="bank_name" value="Bank *" />
                        <x-text-input id="bank_name" name="bank_name" class="mt-1 block w-full" placeholder="BCA / BRI / Mandiri / dll." :value="old('bank_name', $setting->bank_name)" required />
                    </div>
                    <div>
                        <x-input-label for="bank_account" value="Nomor Rekening *" />
                        <x-text-input id="bank_account" name="bank_account" class="mt-1 block w-full font-mono" :value="old('bank_account', $setting->bank_account)" required />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="bank_account_name" value="Nama Pemilik Rekening *" />
                        <x-text-input id="bank_account_name" name="bank_account_name" class="mt-1 block w-full" :value="old('bank_account_name', $setting->bank_account_name)" required />
                    </div>

                    <div>
                        <x-input-label for="timezone" value="Timezone" />
                        <x-text-input id="timezone" name="timezone" class="mt-1 block w-full" :value="old('timezone', $setting->timezone)" />
                    </div>
                    <div>
                        <x-input-label for="currency" value="Mata Uang" />
                        <x-text-input id="currency" name="currency" class="mt-1 block w-full" :value="old('currency', $setting->currency)" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="submit" class="rounded-lg bg-blue-600 hover:bg-blue-700 px-6 py-2.5 font-semibold text-white shadow-sm">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
