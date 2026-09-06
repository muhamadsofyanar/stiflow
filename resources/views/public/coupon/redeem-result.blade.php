<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Redeem Kupon</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-2xl shadow-lg border p-8 text-center">
            @if($success)
            <div class="mx-auto mb-6 flex items-center justify-center h-20 w-20 rounded-full bg-green-100">
                <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Kupon Berhasil Diterapkan!</h1>
            <p class="text-green-600 font-semibold mb-4">Kode: {{ $code }}</p>
            <p class="text-gray-600">{{ $message }}</p>
            @else
            <div class="mx-auto mb-6 flex items-center justify-center h-20 w-20 rounded-full bg-red-100">
                <svg class="h-10 w-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Kupon Tidak Berlaku</h1>
            @if($code)
            <p class="text-red-600 font-semibold mb-4">Kode: {{ $code }}</p>
            @endif
            <p class="text-gray-600">{{ $message ?: 'Kupon tidak ditemukan, sudah kadaluarsa, atau melebihi batas penggunaan.' }}</p>
            @endif

            <div class="mt-8 space-y-3">
                <a href="{{ url()->previous() }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                    Kembali ke Checkout
                </a>
                <a href="{{ url('/') }}" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg transition">
                    Ke Beranda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
