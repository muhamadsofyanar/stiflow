@php($item = $template ?? null)
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" value="Nama template *" />
        <x-text-input id="name" name="name" class="mt-1 w-full" required :value="old('name', $item?->name)" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="channel" value="Channel *" />
        <select id="channel" name="channel" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800" required>
            @foreach(['email', 'whatsapp', 'sms', 'telegram', 'internal'] as $value)
                <option value="{{ $value }}" @selected(old('channel', $item?->channel?->value ?? 'whatsapp') === $value)>{{ ucfirst($value) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="template_type" value="Tipe template *" />
        <x-text-input id="template_type" name="template_type" class="mt-1 w-full" required :value="old('template_type', $item?->template_type ?? 'general')" />
    </div>
    <div>
        <x-input-label for="language_code" value="Kode bahasa *" />
        <x-text-input id="language_code" name="language_code" class="mt-1 w-full" required :value="old('language_code', $item?->language_code ?? 'id')" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="subject_line" value="Subjek" />
        <x-text-input id="subject_line" name="subject_line" class="mt-1 w-full" :value="old('subject_line', $item?->subject_line)" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="content_body" value="Isi pesan *" />
        <textarea id="content_body" name="content_body" rows="8" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800" required>{{ old('content_body', $item?->content_body) }}</textarea>
        <x-input-error :messages="$errors->get('content_body')" class="mt-2" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="placeholders_json" value="Placeholder JSON" />
        <textarea id="placeholders_json" name="placeholders_json" rows="3" class="mt-1 w-full font-mono text-sm rounded-lg border-gray-300 dark:bg-gray-800">{{ old('placeholders_json', $item?->placeholders_json ? json_encode($item->placeholders_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        <x-input-error :messages="$errors->get('placeholders_json')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="external_template_provider_id" value="ID template provider" />
        <x-text-input id="external_template_provider_id" name="external_template_provider_id" class="mt-1 w-full" :value="old('external_template_provider_id', $item?->external_template_provider_id)" />
    </div>
    <div class="flex flex-col justify-center gap-3 pt-6">
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="has_approved_external_template" value="1" @checked(old('has_approved_external_template', $item?->has_approved_external_template))><span>Sudah disetujui provider</span></label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item?->is_active ?? true))><span>Template aktif</span></label>
    </div>
</div>
