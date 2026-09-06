<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ $template->name }}</h2><a href="{{ route('admin.templates.edit', $template) }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg">Edit</a></div>
    </x-slot>
    <div class="py-10"><div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                <div><dt class="text-gray-500">Channel</dt><dd class="font-semibold">{{ $template->channel?->value }}</dd></div>
                <div><dt class="text-gray-500">Tipe</dt><dd class="font-semibold">{{ $template->template_type }}</dd></div>
                <div><dt class="text-gray-500">Bahasa</dt><dd class="font-semibold">{{ $template->language_code }}</dd></div>
                <div><dt class="text-gray-500">Status</dt><dd class="font-semibold">{{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-gray-500">Subjek</dt><dd class="font-semibold">{{ $template->subject_line ?: '-' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-gray-500">Isi pesan</dt><dd class="mt-2 whitespace-pre-wrap rounded-lg bg-gray-50 dark:bg-gray-900 p-4">{{ $template->content_body }}</dd></div>
            </dl>
        </div>
    </div></div>
</x-app-layout>
