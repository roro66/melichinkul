<?php

namespace App\Livewire\Conductores;

use App\Models\Driver;
use App\Models\DriverDocument;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class DriverDocuments extends Component
{
    use WithFileUploads;

    public int $driverId;

    public string $name = '';

    public $file = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'file' => [
                'required',
                'file',
                'max:10240', // 10 MB
                'mimes:jpg,jpeg,png,gif,webp,pdf',
            ],
        ];
    }

    protected $messages = [
        'name.required' => 'Indica de qué se trata el documento (ej. Carnet, Licencia, Certificado de antecedentes).',
        'file.required' => 'Selecciona un archivo.',
        'file.mimes' => 'Solo se permiten imágenes (JPG, PNG, GIF, WebP) o PDF.',
        'file.max' => 'El archivo no puede superar 10 MB.',
    ];

    public function mount(int $driverId): void
    {
        $this->driverId = $driverId;
    }

    public function save(): void
    {
        $this->validate();

        $driver = Driver::findOrFail($this->driverId);

        $extension = $this->file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $directory = 'drivers/' . $this->driverId;

        $path = $this->file->storeAs($directory, $filename, 'public');

        $driver->documents()->create([
            'name' => trim($this->name),
            'file_path' => $path,
            'original_name' => $this->file->getClientOriginalName(),
        ]);

        $this->reset(['name', 'file']);
        session()->flash('documents_success', 'Documento subido correctamente.');
    }

    public function deleteDocument(int $id): void
    {
        $document = DriverDocument::where('driver_id', $this->driverId)->findOrFail($id);
        $document->delete();
        session()->flash('documents_success', 'Documento eliminado.');
    }

    public function render()
    {
        $driver = Driver::with('documents')->findOrFail($this->driverId);

        return view('livewire.conductores.driver-documents', [
            'documents' => $driver->documents()->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
