<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Order #{{ $order->number }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="flex flex-wrap gap-4 justify-between items-start">
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-wider">Order</div>
                                <div class="font-mono text-xl font-bold text-gray-900 dark:text-white">{{ $order->number }}</div>
                                <div class="text-xs text-gray-500 mt-1">Dibuat: {{ $order->created_at }} · Kadaluarsa: {{ $order->expires_at ?? '-' }}</div>
                            </div>
                            <x-status-badge :value="$order->status" class="text-sm px-3 py-1" />
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4 mt-6 text-sm">
                            <div>
                                <div class="text-gray-500">Promotor</div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $order->user?->name ?? '-' }}</div>
                                <div class="text-gray-600 dark:text-gray-300">{{ $order->user?->email ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Kode Tujuan Voucher (snapshot)</div>
                                <div class="font-mono font-bold text-gray-900 dark:text-white">{{ $order->promotor_code_snapshot ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-bold text-lg mb-4 text-gray-900 dark:text-white">Detail Produk</h3>
                        @foreach($order->items as $item)
                            <div class="border-t border-gray-100 dark:border-gray-700 pt-4 mt-2">
                                <div class="flex justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ $item->product_snapshot_json['name'] ?? 'Voucher STIFIN' }}
                                        </div>
                                        <div class="text-xs text-gray-500">Tipe: {{ $item->fulfillment_type?->value ?? 'voucher' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $item->quantity }} × Rp {{ number_format((float)$item->unit_price, 0, ',', '.') }}</div>
                                        <div class="text-lg font-bold text-blue-700 dark:text-blue-400">Rp {{ number_format((float)$item->total, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                @if($item->fulfillment)
                                    <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700/30 text-sm space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Status Pemenuhan</span>
                                            <x-status-badge :value="$item->fulfillment->status" />
                                        </div>
                                        @if($item->fulfillment->voucherFulfillment)
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <div class="text-gray-500">Saldo Sebelum (Paid/Free)</div>
                                                    <div class="font-mono font-semibold">{{ $item->fulfillment->voucherFulfillment->balance_before_paid ?? '-' }} / {{ $item->fulfillment->voucherFulfillment->balance_before_free ?? '-' }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-500">Saldo Sesudah</div>
                                                    <div class="font-mono font-semibold">{{ $item->fulfillment->voucherFulfillment->balance_after_paid ?? '-' }} / {{ $item->fulfillment->voucherFulfillment->balance_after_free ?? '-' }}</div>
                                                </div>
                                            </div>
                                            @if($item->fulfillment->voucherFulfillment->stifin_reference)
                                                <div class="text-xs text-gray-500">Dispos/Reference: <code class="bg-gray-200 dark:bg-gray-700 rounded px-1.5">{{ $item->fulfillment->voucherFulfillment->stifin_reference }}</code></div>
                                            @endif
                                        @endif
                                        @if($item->fulfillment->stifinOperations->count())
                                            <div class="mt-2">
                                                <div class="text-gray-500 mb-1">Pemanggilan API STIFIN:</div>
                                                <div class="space-y-1 text-xs">
                                                    @foreach($item->fulfillment->stifinOperations as $op)
                                                        <div class="flex items-center gap-2 font-mono">
                                                            <span class="w-2 h-2 rounded-full {{ in_array($op->outcome?->value, ['success']) ? 'bg-green-500' : (in_array($op->outcome?->value, ['ambiguous_timeout','ambiguous_transport'], true) ? 'bg-orange-500' : 'bg-red-500') }}"></span>
                                                            <span class="w-28 truncate">{{ $op->operation_type }}</span>
                                                            <span class="w-24 truncate text-gray-700 dark:text-gray-300">{{ $op->outcome?->value ?? '-' }}</span>
                                                            <span class="text-gray-500">HTTP {{ $op->http_status_code ?? '-' }}</span>
                                                            <span class="ml-auto text-gray-400">{{ $op->completed_at }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        @if($item->fulfillment->error_message)
                                            <div class="text-red-600 dark:text-red-400 text-xs">Error: {{ $item->fulfillment->error_message }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white">Ringkasan</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="font-semibold">Rp {{ number_format((float)$order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Diskon</span>
                                <span class="font-semibold">Rp {{ number_format((float)$order->discount, 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-gray-100 dark:border-gray-700 my-3"></div>
                            <div class="flex justify-between text-lg">
                                <span class="font-bold text-gray-900 dark:text-white">Total</span>
                                <span class="font-extrabold text-blue-700 dark:text-blue-400">Rp {{ number_format((float)$order->total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    @php
                        $proof = collect($order->paymentAttempts)->map(fn($a)=>$a->proof)->filter()->first();
                    @endphp
                    @if($proof)
                        @livewire(\App\Livewire\PaymentVerifyCard::class, ['proof' => $proof])
                    @else
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 text-sm text-yellow-800 dark:text-yellow-300">
                            Belum ada bukti transfer yang diunggah promotor.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
