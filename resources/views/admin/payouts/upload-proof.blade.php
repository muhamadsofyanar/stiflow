<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Upload Bukti Transfer - Payout {{ $payout->batch_number }}
            </h2>
            <a href="{{ route('admin.payouts.show', $payout) }}" class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Kembali ke Detail Payout
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-blue-50 border border-blue-200 sm:rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600">Batch Payout</p>
                        <p class="font-bold text-blue-800">{{ $payout->batch_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Jumlah Entry</p>
                        <p class="font-bold text-blue-800">{{ $payout->entry_count }} komisi</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Status Saat Ini</p>
                        <p class="font-bold text-blue-800">{{ strtoupper($payout->status->value) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Total Dibayar</p>
                        <p class="font-bold text-green-700">Rp {{ number_format($payout->total_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if(!in_array($payout->status, [\App\Enums\PayoutStatus::Locked, \App\Enums\PayoutStatus::Approved], true))
                <div class="p-6 bg-yellow-50 border-b border-yellow-200">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-yellow-800">Payout belum dapat dibayar</p>
                            <p class="text-sm text-yellow-700">Payout harus dalam status Locked atau Approved sebelum upload bukti transfer.</p>
                        </div>
                    </div>
                </div>
                @else
                <form method="POST" action="{{ route('admin.payouts.upload-proof', $payout) }}" enctype="multipart/form-data" class="p-6">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Bukti Transfer *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required class="mt-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-2 text-xs text-gray-500">Format: JPG, JPEG, PNG, PDF | Max: 5MB</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Tujuan</label>
                            <input type="text" name="bank_name" value="{{ old('bank_name', $payout->bank_name) }}"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Nama bank yang digunakan untuk transfer</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pengirim</label>
                            <input type="text" name="sender_name" value="{{ old('sender_name') }}"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                            <p class="text-xs text-gray-500 mt-1">Nama di rekening pengirim</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Pengirim</label>
                            <input type="text" name="sender_bank" value="{{ old('sender_bank') }}"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Transfer *</label>
                            <input type="number" name="transfer_amount" value="{{ old('transfer_amount', $payout->total_amount) }}" required min="0" step="any"
                                class="w-full rounded-md border-gray-300 border px-3 py-2 font-semibold text-green-700">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Transfer *</label>
                            <input type="datetime-local" name="transfer_time" value="{{ old('transfer_time', now()->format('Y-m-d\TH:i')) }}" required
                                class="w-full rounded-md border-gray-300 border px-3 py-2">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Admin</label>
                            <textarea name="notes" rows="2"
                                class="w-full rounded-md border-gray-300 border px-3 py-2">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t">
                        <a href="{{ route('admin.payouts.show', $payout) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-md font-medium">
                            Batal
                        </a>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-semibold" onclick="return confirm('Upload bukti dan tandai payout sebagai Paid?');">
                            Upload & Tandai Sudah Dibayar
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
