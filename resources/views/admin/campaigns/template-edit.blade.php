<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Edit Message Template</h2></x-slot>
    <div class="py-10"><div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.templates.update', $template) }}" class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm space-y-6">
            @csrf @method('PUT')
            @include('admin.campaigns._template-form')
            <div class="flex justify-end gap-3"><a href="{{ route('admin.templates.index') }}" class="px-4 py-2">Batal</a><x-primary-button>Simpan Perubahan</x-primary-button></div>
        </form>
    </div></div>
</x-app-layout>
