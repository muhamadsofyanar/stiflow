<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Audit Logs</h2></x-slot>
    <div class="py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-sm"><thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-6 py-3 text-left">Waktu</th><th class="px-6 py-3 text-left">Aktor</th><th class="px-6 py-3 text-left">Aksi</th><th class="px-6 py-3 text-left">Subjek</th><th class="px-6 py-3 text-left">IP</th></tr></thead><tbody class="divide-y dark:divide-gray-700">
        @forelse($logs as $log)<tr><td class="px-6 py-4">{{ $log->created_at?->format('d-m-Y H:i:s') }}</td><td class="px-6 py-4">{{ $log->actor?->name ?? 'Sistem' }}</td><td class="px-6 py-4 font-mono text-xs">{{ $log->action?->value ?? $log->action }}</td><td class="px-6 py-4">{{ class_basename($log->subject_type ?? '-') }} #{{ $log->subject_id ?? '-' }}</td><td class="px-6 py-4">{{ $log->ip_address ?? '-' }}</td></tr>@empty<tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">Belum ada audit log.</td></tr>@endforelse
        </tbody></table><div class="px-6 py-4">{{ $logs->links() }}</div>
    </div></div></div>
</x-app-layout>
