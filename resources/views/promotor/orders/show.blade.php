<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Order #{{ $order->number }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('status'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg px-4 py-3 text-green-800 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg px-4 py-3 text-red-800 dark:text-red-300 space-y-1">
                    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-wider">Order</div>
                                <div class="font-mono text-xl font-bold text-gray-900 dark:text-white">{{ $order->number }}</div>
                                <div class="text-xs text-gray-500 mt-1">Kode Voucher Tujuan: <span class="font-bold text-gray-800 dark:text-white">{{ $order->promotor_code_snapshot ?? '-' }}</span></div>
                            </div>
                            <x-status-badge :value="$order->status" class="text-sm px-3 py-1" />
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-bold text-lg mb-4 text-gray-900 dark:text-white">Item Order</h3>
                        @foreach($order->items as $item)
                            <div class="py-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $item->product_snapshot_json['name'] ?? 'Voucher STIFIN' }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Kuantitas × Harga: {{ $item->quantity }} × Rp {{ number_format((float)$item->unit_price, 0, ',', '.') }}</div>
                                    @if($item->fulfillment)
                                        <div class="mt-2 inline-flex items-center gap-2 text-xs">
                                            <span class="text-gray-500">Pemenuhan:</span>
                                            <x-status-badge :value="$item->fulfillment->status" />
                                            @if($item->fulfillment->voucherFulfillment?->stifin_reference)
                                                <span class="text-gray-500">Ref:</span><code class="bg-gray-100 dark:bg-gray-700 rounded px-1.5">{{ $item->fulfillment->voucherFulfillment->stifin_reference }}</code>
                                            @endif
                                        </div>
                                    @endif
                                    @if($order->status == \App\Enums\OrderStatus::NeedsReview)
                                        <div class="mt-3 rounded-lg bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 text-orange-800 dark:text-orange-300 px-3 py-2 text-xs">
                                            Pemenuhan voucher dalam status Perlu Review. Admin cabang akan verifikasi & lanjutkan secara manual.
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="font-extrabold text-blue-700 dark:text-blue-400 text-lg">Rp {{ number_format((float)$item->total, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm text-sm">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white">Total Tagihan</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold">Rp {{ number_format((float)$order->subtotal, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Diskon</span><span class="font-semibold">Rp {{ number_format((float)$order->discount, 0, ',', '.') }}</span></div>
                            <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                            <div class="flex justify-between text-lg"><span class="font-bold text-gray-900 dark:text-white">TOTAL</span><span class="font-extrabold text-blue-700 dark:text-blue-400">Rp {{ number_format((float)$order->total, 0, ',', '.') }}</span></div>
                        </div>
                    </div>

                    @php $proof = $order->paymentAttempts->map(fn($a)=>$a->proof)->filter()->first(); @endphp
                    @if($proof)
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-bold text-gray-900 dark:text-white">Bukti Pembayaran</h3>
                                <x-status-badge :value="$proof->review_status" />
                            </div>
                            @if(str_starts_with($proof->mime_type ?? '', 'image/'))
                                <a href="{{ route('payment-proofs.download', $proof) }}" target="_blank">
                                    <img src="{{ route('payment-proofs.download', $proof) }}" alt="bukti" class="w-full rounded-lg border border-gray-200 dark:border-gray-700" />
                                </a>
                            @else
                                <a href="{{ route('payment-proofs.download', $proof) }}" class="text-blue-600 font-semibold underline">Unduh bukti →</a>
                            @endif
                            <div class="mt-3 text-xs text-gray-500 space-y-0.5">
                                <div>Pengirim: <b>{{ $proof->sender_name ?? '-' }}</b> @ {{ $proof->sender_bank ?? '-' }}</div>
                                <div>Jumlah: <b>Rp {{ number_format((float)$proof->transfer_amount, 0, ',', '.') }}</b></div>
                                <div>Waktu Transfer: {{ $proof->transfer_time ?? '-' }}</div>
                                <div>Diunggah: {{ $proof->created_at ?? '-' }}</div>
                                @if($proof->review_note)
                                    <div class="pt-2 mt-2 border-t border-gray-100 dark:border-gray-700 text-gray-700 dark:text-gray-300">Catatan admin: <i>{{ $proof->review_note }}</i></div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(in_array($order->status, [\App\Enums\OrderStatus::PendingPayment, \App\Enums\OrderStatus::PaymentSubmitted], true))
                        <form method="POST" action="{{ route('promotor.orders.upload-proof', $order) }}" enctype="multipart/form-data"
                              class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm text-sm">
                            @csrf
                            <h3 class="font-bold mb-4 text-gray-900 dark:text-white">@if($proof) Ganti @else Unggah @endif Bukti Transfer</h3>
                            <div class="rounded-xl border-2 border-dashed border-blue-300 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/10 p-4 mb-4 text-xs space-y-1">
                                <div class="text-blue-800 dark:text-blue-300 font-bold">Transfer ke:</div>
                                <div class="font-bold text-gray-900 dark:text-white text-base">{{ $branch?->bank_name ?? '- Bank -' }}</div>
                                <div class="font-mono text-2xl font-extrabold text-gray-900 dark:text-white">{{ $branch?->bank_account ?? '- - -' }}</div>
                                <div class="text-gray-700 dark:text-gray-300">a/n <b>{{ $branch?->bank_account_name ?? '- - -' }}</b></div>
                                <div class="pt-2 border-t border-blue-200 dark:border-blue-900 mt-2">
                                    ⚠ Transfer tepat <b class="text-gray-900 dark:text-white">Rp {{ number_format((float)$order->total, 0, ',', '.') }}</b> untuk verifikasi otomatis.
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-xs font-medium mb-1">Bank Pengirim *</label>
                                    <input type="text" name="sender_bank" required value="{{ old('sender_bank', 'BCA') }}"
                                           class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white dark:border-gray-700" />
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-xs font-medium mb-1">Nama Pengirim *</label>
                                    <input type="text" name="sender_name" required value="{{ old('sender_name', auth()->user()->name) }}"
                                           class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white dark:border-gray-700" />
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-xs font-medium mb-1">Jumlah Transfer *</label>
                                    <input type="number" name="transfer_amount" required value="{{ old('transfer_amount', (string)$order->total) }}"
                                           class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white dark:border-gray-700" />
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-xs font-medium mb-1">Waktu Transfer *</label>
                                    <input type="datetime-local" name="transfer_time" required
                                           value="{{ old('transfer_time', date('Y-m-d\TH:i')) }}"
                                           class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-900 dark:text-white dark:border-gray-700" />
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-medium mb-1">File Bukti Transfer (JPG/PNG, max 5MB) *</label>
                                <input type="file" name="proof" required accept="image/jpeg,image/png"
                                       class="w-full rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-sm" />
                                <x-input-error :messages="$errors->get('proof')" class="mt-1" />
                            </div>
                            <button type="submit" class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 shadow-sm">
                                @if($proof) Ganti & Kirim Bukti Pembayaran @else Kirim Bukti Pembayaran @endif
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
