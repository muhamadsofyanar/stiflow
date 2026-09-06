<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Kontak: {{ $contact->full_name }}</h2>
            <a href="{{ route('admin.contacts.show', $contact) }}" class="inline-flex items-center px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">← Batal</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.contacts.update', $contact) }}" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-8 space-y-6">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-input-label for="full_name" value="Nama Lengkap *" />
                        <x-text-input id="full_name" name="full_name" class="mt-1 w-full" required :value="old('full_name', $contact->full_name)" />
                        <x-input-error :messages="$errors->get('full_name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 w-full" :value="old('email', $contact->email)" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="Phone" />
                        <x-text-input id="phone" name="phone" class="mt-1 w-full" :value="old('phone', $contact->phone)" />
                    </div>
                    <div>
                        <x-input-label for="whatsapp" value="WhatsApp" />
                        <x-text-input id="whatsapp" name="whatsapp" class="mt-1 w-full" :value="old('whatsapp', $contact->whatsapp)" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-lg">
                            <option value="">Pilih status...</option>
                            @foreach(['lead','prospect','qualified','converted','inactive'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $contact->status?->value ?? $contact->status) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('admin.contacts.show', $contact) }}" class="inline-flex items-center px-4 py-2 text-sm border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Batal</a>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
