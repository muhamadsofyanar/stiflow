<div class="border border-gray-200 dark:border-gray-600 rounded-xl p-6">
    <h3 class="font-bold text-gray-900 dark:text-white mb-4">📥 Import Kontak (CSV)</h3>

    <div x-data="{ uploading: false }" class="space-y-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Pilih File CSV</label>
            <input type="file"
                   wire:model="file"
                   wire:loading.attr="disabled"
                   accept=".csv,.txt"
                   class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900 dark:file:text-blue-200">
            @error('file') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
        </div>

        @if($previewReady)
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Preview ({{ count($previewRows) }} baris pertama)
                    </div>
                    <button wire:click="confirmImport"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                        ✓ Import Data Ini
                    </button>
                </div>

                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-2 py-2">#</th>
                                <th class="px-2 py-2">Status</th>
                                @foreach($headers as $h)
                                    <th class="px-2 py-2 text-left">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewRows as $i => $row)
                                <tr class="border-t border-gray-100 dark:border-gray-700 {{ $row['valid'] ? 'bg-green-50/50 dark:bg-green-900/20' : 'bg-red-50/50 dark:bg-red-900/20' }}">
                                    <td class="px-2 py-2 font-bold text-gray-500">{{ $i }}</td>
                                    <td class="px-2 py-2">
                                        @if($row['valid'])
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-bold">VALID</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full font-bold">INVALID</span>
                                        @endif
                                    </td>
                                    @foreach($headers as $h)
                                        <td class="px-2 py-2 text-gray-700 dark:text-gray-200 truncate max-w-[200px]">
                                            {{ $row['data'][$h] ?? '' }}
                                            @if(!$row['valid'] && $loop->first)
                                                <div class="text-[10px] text-red-600 mt-1">
                                                    {{ implode(', ', $row['errors']) }}
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-xs text-gray-500">
                    {{ count($invalidRows) }} baris invalid. Total preview: {{ count($previewRows) }} baris.
                </div>
            </div>
        @endif
    </div>

    @if(session('status'))
        <div class="mt-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-3 text-green-700 dark:text-green-300 text-sm">
            {{ session('status') }}
        </div>
    @endif
</div>
