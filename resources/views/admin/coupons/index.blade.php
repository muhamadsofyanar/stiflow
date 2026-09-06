<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Kupon</h2>
            <a href="{{ route('admin.coupons.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                + Buat Kupon Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <form method="GET" action="{{ route('admin.coupons.index') }}" class="p-6 border-b grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-600">Cari Kode / Deskripsi</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="mt-1 w-full rounded-md border-gray-300 border px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-600">Status</label>
                        <select name="status" class="mt-1 w-full rounded-md border-gray-300 border px-3 py-2 text-sm">
                            <option value="">Semua</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">Filter</button>
                        <a href="{{ route('admin.coupons.index') }}" class="ml-2 bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemakaian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Masa Berlaku</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($coupons as $coupon)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 font-bold">
                                        {{ $coupon->code }}
                                    </span>
                                    @if($coupon->description)
                                    <p class="mt-1 text-xs text-gray-500">{{ $coupon->description }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($coupon->type === 'percent')
                                    <span class="text-purple-600 font-medium">Persentase</span>
                                    @else
                                    <span class="text-green-600 font-medium">Fixed</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    @if($coupon->type === 'percent')
                                    {{ $coupon->value }}%
                                    @if($coupon->max_discount)
                                    <div class="text-xs text-gray-500">Max Rp {{ number_format($coupon->max_discount, 0, ',', '.') }}</div>
                                    @endif
                                    @else
                                    Rp {{ number_format($coupon->value, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="font-medium">{{ $coupon->redemptions()->count() }} / {{ $coupon->max_redemptions_global ?? 'Unlimited' }}</div>
                                    @if($coupon->min_order_total > 0)
                                    <div class="text-xs text-gray-500">Min order: Rp {{ number_format($coupon->min_order_total, 0, ',', '.') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($coupon->starts_at)
                                    Mulai: {{ $coupon->starts_at->format('d M Y') }}<br>
                                    @endif
                                    @if($coupon->expires_at)
                                    Sampai: {{ $coupon->expires_at->format('d M Y') }}
                                    @else
                                    Tanpa batas
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match($coupon->status) {
                                            'active' => 'bg-green-100 text-green-800',
                                            'inactive' => 'bg-gray-100 text-gray-800',
                                            'expired' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusLabel = match($coupon->status) {
                                            'active' => 'Aktif',
                                            'inactive' => 'Tidak Aktif',
                                            'expired' => 'Kadaluarsa',
                                            default => $coupon->status,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                    <form method="POST" action="{{ route('admin.coupons.toggle-status', $coupon) }}" class="inline mr-3" onsubmit="return confirm('Ubah status kupon?');">
                                        @csrf
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">Toggle</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Hapus kupon ini? Data pemakaian akan hilang.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada kupon yang dibuat.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t">
                    {{ $coupons->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
