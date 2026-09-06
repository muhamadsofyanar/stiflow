<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Kursus & Lesson</h2>
            <a href="{{ route('admin.courses.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold">Tambah Kursus</a>
        </div>
    </x-slot>
    <div class="py-10"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-6 py-3 text-left">Judul</th><th class="px-6 py-3 text-left">Modul</th><th class="px-6 py-3 text-left">Status</th><th class="px-6 py-3"></th></tr></thead>
                <tbody class="divide-y dark:divide-gray-700">
                @forelse($courses as $course)
                    <tr><td class="px-6 py-4 font-semibold">{{ $course->title }}</td><td class="px-6 py-4">{{ $course->modules_count }}</td><td class="px-6 py-4">{{ $course->is_published ? 'Terbit' : 'Draft' }}</td><td class="px-6 py-4 text-right"><a class="text-blue-600" href="{{ route('admin.courses.show', $course) }}">Detail</a> · <a class="text-amber-600" href="{{ route('admin.courses.edit', $course) }}">Edit</a></td></tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Belum ada kursus.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">{{ $courses->links() }}</div>
        </div>
    </div></div>
</x-app-layout>
