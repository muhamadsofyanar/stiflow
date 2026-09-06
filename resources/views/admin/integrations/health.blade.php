<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Provider Health Dashboard</h2>
            <form method="POST" action="{{ route('admin.integrations.health.run') }}" class="inline">
                @csrf
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm font-medium inline-flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Test Semua Koneksi
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-gray-400">
                    <p class="text-xs text-gray-500 uppercase">Total</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $healthSummary['total'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-xs text-green-600 uppercase">Healthy</p>
                    <p class="text-3xl font-bold text-green-700">{{ $healthSummary['healthy'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-yellow-500">
                    <p class="text-xs text-yellow-600 uppercase">Degraded</p>
                    <p class="text-3xl font-bold text-yellow-700">{{ $healthSummary['degraded'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-red-500">
                    <p class="text-xs text-red-600 uppercase">Offline</p>
                    <p class="text-3xl font-bold text-red-700">{{ $healthSummary['offline'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-red-600">
                    <p class="text-xs text-red-600 uppercase">Error</p>
                    <p class="text-3xl font-bold text-red-800">{{ $healthSummary['error'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-gray-500">
                    <p class="text-xs text-gray-600 uppercase">Disabled</p>
                    <p class="text-3xl font-bold text-gray-700">{{ $healthSummary['disabled'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-400">
                    <p class="text-xs text-blue-600 uppercase">Untested</p>
                    <p class="text-3xl font-bold text-blue-700">{{ $healthSummary['untested'] }}</p>
                </div>
            </div>

            @if($lastCheckAt)
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-6 text-sm text-blue-800">
                    Health check terakhir dijalankan: <strong>{{ $lastCheckAt->diffForHumans() }}</strong> ({{ $lastCheckAt->format('d M Y H:i:s') }})
                </div>
            @endif

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 border">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Provider</label>
                    <select name="category" class="w-full rounded-md border-gray-300 text-sm">
                        <option value="">Semua</option>
                        @foreach(['whatsapp', 'email', 'sms', 'payment', 'stifin_api', 'license'] as $c)
                            <option value="{{ $c }}" {{ $categoryFilter === $c ? 'selected' : '' }}>{{ strtoupper($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full rounded-md border-gray-300 text-sm">
                        <option value="">Semua</option>
                        @foreach(['healthy', 'degraded', 'offline', 'error', 'disabled', 'configured'] as $s)
                            <option value="{{ $s }}" {{ $statusFilter === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-md text-sm">Filter</button>
                    <a href="{{ route('admin.integrations.health') }}" class="ml-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">Reset</a>
                </div>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori / Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Terakhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sukses Terakhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failures</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($connections as $conn)
                                @php
                                    $statusValue = is_object($conn->status) ? $conn->status->value : $conn->status;
                                    $statusMap = [
                                        'healthy' => ['bg-green-100 text-green-800', '✅'],
                                        'degraded' => ['bg-yellow-100 text-yellow-800', '⚠️'],
                                        'offline' => ['bg-red-100 text-red-800', '🔴'],
                                        'error' => ['bg-red-100 text-red-700', '❌'],
                                        'disabled' => ['bg-gray-200 text-gray-600', '🚫'],
                                        'configured' => ['bg-blue-100 text-blue-800', '🔵'],
                                    ];
                                    [$class, $icon] = $statusMap[$statusValue] ?? ['bg-gray-100 text-gray-600', '❔'];
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $conn->display_name }}</div>
                                                <div class="text-xs text-gray-500">ID #{{ $conn->id }} @if($conn->is_primary) <span class="text-indigo-600 font-mono">[PRIMARY]</span> @endif</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mr-2">
                                            {{ is_object($conn->provider_category) ? $conn->provider_category->value : strtoupper($conn->provider_category) }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $conn->provider_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">
                                            {{ $icon }} {{ strtoupper($statusValue) }}
                                        </span>
                                        @if($conn->last_error_message)
                                            <div class="mt-1 text-xs text-red-600 max-w-xs truncate" title="{{ $conn->last_error_message }}">
                                                {{ $conn->last_error_message }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @if($conn->last_tested_at)
                                            {{ $conn->last_tested_at->format('d M Y H:i') }}
                                            <div class="text-xs text-gray-400">{{ $conn->last_tested_at->diffForHumans() }}</div>
                                        @else
                                            <span class="text-gray-400 italic">Belum pernah</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($conn->last_success_at)
                                            <span class="text-green-700">{{ $conn->last_success_at->format('d M Y H:i') }}</span>
                                            <div class="text-xs text-gray-400">{{ $conn->last_success_at->diffForHumans() }}</div>
                                        @else
                                            <span class="text-red-500 italic">Belum pernah sukses</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($conn->failure_count > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                                {{ $conn->failure_count }}x gagal
                                            </span>
                                            @if($conn->degraded_since_at)
                                                <div class="text-xs text-red-600 mt-1">Sejak: {{ $conn->degraded_since_at->format('d M Y') }}</div>
                                            @endif
                                        @else
                                            <span class="text-green-600 text-sm">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form method="POST" action="{{ route('admin.integrations.health.single', $conn) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900">Test Sekarang</button>
                                        </form>
                                        <a href="{{ route('admin.integrations.edit', $conn) }}" class="ml-3 text-gray-600 hover:text-gray-900">Config</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        Tidak ada integration connection.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
