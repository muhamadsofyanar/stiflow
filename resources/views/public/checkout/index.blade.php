<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ $product->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="{{ url('/') }}" class="text-blue-600 hover:text-blue-800">&larr; Kembali ke Beranda</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Ringkasan Pesanan</h2>

                    <div class="space-y-4">
                        @foreach($cart['items'] as $item)
                        <div class="flex items-center justify-between border-b pb-4">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $item['name'] }}</h3>
                                <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold">Rp {{ number_format($item['unit_price'] * $item['quantity'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @endforeach

                        @if(count($cart['bumps']) > 0)
                        <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                            <h4 class="font-semibold text-green-800 mb-2">Order Bump Ditambahkan</h4>
                            @foreach($cart['bumps'] as $bump)
                            <div class="flex items-center justify-between">
                                <p class="text-green-700">{{ $bump['name'] }}</p>
                                <p class="font-medium text-green-800">Rp {{ number_format($bump['unit_price'], 0, ',', '.') }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    @if(count($orderBumps) > 0)
                    <div class="mt-6">
                        <h3 class="font-semibold text-lg mb-3 text-gray-800">Tambahan Spesial (Order Bump)</h3>
                        <div class="space-y-3">
                            @foreach($orderBumps as $bump)
                            <label class="flex items-start p-4 border rounded-lg hover:bg-blue-50 cursor-pointer transition {{ $selectedBump && $selectedBump->id == $bump->id ? 'border-blue-500 bg-blue-50' : '' }}">
                                <input
                                    type="checkbox"
                                    name="bump_ids[]"
                                    value="{{ $bump->id }}"
                                    form="checkout-form"
                                    class="mt-1 mr-3 h-4 w-4 text-blue-600"
                                    {{ $selectedBump && $selectedBump->id == $bump->id ? 'checked' : '' }}
                                >
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <p class="font-semibold text-gray-900">{{ $bump->bumpProduct?->name ?? 'Order Bump' }}</p>
                                        <p class="font-bold text-green-600">Rp {{ number_format($bump->discount_price ?? ($bump->bumpProduct?->price ?? 0), 0, ',', '.') }}</p>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">{{ $bump->description ?? 'Penawaran terbatas - tambahkan sekarang!' }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-900">Kode Kupon</h2>
                    @livewire('checkout-coupon-applier')
                </div>

                <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="bg-white rounded-xl shadow-sm border p-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if($variant)
                    <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                    @endif

                    <h2 class="text-xl font-bold mb-4 text-gray-900">Data Pembeli</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                            <input type="text" name="billing_name" required class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="billing_email" required class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp</label>
                            <input type="tel" name="billing_phone" class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Promotor</label>
                            <input type="text" name="promotor_code" class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran *</label>
                        <select name="payment_method" required class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="manual_transfer">Transfer Bank Manual</option>
                            <option value="xendit">Xendit</option>
                            <option value="midtrans">Midtrans</option>
                        </select>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-200"></textarea>
                    </div>

                    <div class="mt-8">
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 px-6 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-lg text-lg">
                            Bayar Sekarang - Rp {{ number_format($cart['total'], 0, ',', '.') }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border p-6 sticky top-8">
                    <h3 class="font-bold text-xl mb-4 text-gray-900">Total Pesanan</h3>
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">Rp {{ number_format($cart['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        @if($cart['discount'] > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600">Diskon Kupon</span>
                            <span class="text-green-600 font-medium">- Rp {{ number_format($cart['discount'], 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($couponApplied)
                        <div class="flex justify-between text-xs text-gray-500 bg-green-50 px-2 py-1 rounded">
                            <span>Kupon: {{ $couponApplied['code'] }}</span>
                            <a href="{{ route('coupon.remove') }}" class="text-red-500 hover:text-red-700">Hapus</a>
                        </div>
                        @endif
                        <div class="border-t pt-2 mt-3">
                            <div class="flex justify-between font-bold text-lg">
                                <span class="text-gray-900">Total</span>
                                <span class="text-blue-600">Rp {{ number_format($cart['total'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 space-y-1">
                        <p>&#10003; Pembayaran aman & terenkripsi</p>
                        <p>&#10003; Bukti transfer akan diverifikasi maks 1x24 jam</p>
                        <p>&#10003; Akses produk otomatis setelah verifikasi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
