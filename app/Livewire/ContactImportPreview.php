<?php

namespace App\Livewire;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContactImportPreview extends Component
{
    use WithFileUploads;

    public ?UploadedFile $file = null;

    public bool $previewReady = false;

    public array $previewRows = [];

    public array $invalidRows = [];

    public array $headers = [];

    public function updatedFile(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $this->generatePreview();
    }

    private function generatePreview(): void
    {
        if (! $this->file) {
            return;
        }

        $handle = fopen($this->file->getRealPath(), 'r');
        if (! $handle) {
            return;
        }

        $this->headers = fgetcsv($handle) ?: [];
        $this->previewRows = [];
        $this->invalidRows = [];

        $rowNum = 1;
        while (($data = fgetcsv($handle)) !== false && $rowNum <= 10) {
            $row = array_combine($this->headers, array_pad($data, count($this->headers), null));

            $validator = Validator::make($row, [
                'full_name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                $this->invalidRows[$rowNum] = $validator->errors()->all();
            }

            $this->previewRows[$rowNum] = [
                'data' => $row,
                'valid' => ! $validator->fails(),
                'errors' => $validator->errors()->all(),
            ];

            $rowNum++;
        }

        fclose($handle);
        $this->previewReady = true;
    }

    public function confirmImport(): void
    {
        session()->flash('status', 'Import diproses (simulasi). Total preview: '.count($this->previewRows).', invalid: '.count($this->invalidRows));
    }

    public function render()
    {
        return view('livewire.contact-import-preview');
    }
}
