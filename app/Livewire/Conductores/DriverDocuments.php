<?php

namespace App\Livewire\Conductores;

use App\Models\Driver;
use App\Models\DriverDocument;
use App\Services\LicenseAlertService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class DriverDocuments extends Component
{
    use WithFileUploads;

    public int $driverId;

    public string $name = '';

    public string $kind = 'otro';

    public string $expiration_date = '';

    public $file = null;

    public $reverseFile = null;

    protected function rules(): array
    {
        $fileRules = ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf'];
        $canHaveReverse = DriverDocument::kindCanHaveReverse($this->kind);

        return [
            'name' => ['required', 'string', 'max:100'],
            'kind' => ['required', 'in:otro,carnet,licencia'],
            'expiration_date' => $this->kind === 'licencia'
                ? ['required', 'date', 'after_or_equal:today']
                : ['nullable', 'date'],
            'file' => array_merge(['required'], $fileRules),
            'reverseFile' => $canHaveReverse ? array_merge(['nullable'], $fileRules) : ['nullable'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Indica de qué se trata el documento (ej. Carnet, Licencia, Certificado de antecedentes).',
            'expiration_date.required' => 'Indica la fecha de vencimiento de la licencia vigente.',
            'expiration_date.after_or_equal' => 'La licencia vigente debe vencer hoy o más adelante.',
            'file.required' => 'Selecciona una imagen o PDF.',
            'file.mimes' => 'Solo se permiten imágenes (JPG, PNG, GIF, WebP) o PDF.',
            'file.max' => 'El archivo no puede superar 10 MB.',
            'reverseFile.mimes' => 'El reverso solo puede ser imagen (JPG, PNG, GIF, WebP) o PDF.',
            'reverseFile.max' => 'El reverso no puede superar 10 MB.',
        ];
    }

    public function mount(int $driverId): void
    {
        $this->driverId = $driverId;
    }

    public function updatedKind(string $kind): void
    {
        $defaults = [
            'carnet' => 'Carnet de identidad',
            'licencia' => 'Licencia de conducir',
        ];

        if (! DriverDocument::kindCanHaveReverse($kind)) {
            if (in_array($this->name, $defaults, true)) {
                $this->name = '';
            }
            $this->reverseFile = null;

            return;
        }

        if ($this->name === '' || in_array($this->name, $defaults, true)) {
            $this->name = $defaults[$kind];
        }
    }

    public function save(): void
    {
        $this->validate();

        $driver = Driver::findOrFail($this->driverId);
        [$path, $originalName] = $this->storeUpload($this->file);
        $reversePath = null;
        $reverseOriginalName = null;

        if (DriverDocument::kindCanHaveReverse($this->kind) && $this->reverseFile) {
            [$reversePath, $reverseOriginalName] = $this->storeUpload($this->reverseFile);
        }

        $isLicense = $this->kind === 'licencia';

        $driver->documents()->create([
            'name' => trim($this->name),
            'kind' => $this->kind,
            'expiration_date' => $isLicense ? $this->expiration_date : null,
            'file_path' => $path,
            'original_name' => $originalName,
            'reverse_file_path' => $reversePath,
            'reverse_original_name' => $reverseOriginalName,
        ]);

        $message = 'Documento subido correctamente.';
        if ($isLicense) {
            $driver->update(['license_expiration_date' => $this->expiration_date]);
            $result = app(LicenseAlertService::class)->refreshDriver($driver->fresh(), null, true);
            $message = $result['state'] === 'clear'
                ? 'Licencia vigente cargada. El vencimiento supera los 60 días y se cerró la alerta.'
                : 'Licencia cargada. El vencimiento sigue dentro de los 60 días, así que el aviso continúa.';
        }

        $this->reset(['name', 'kind', 'expiration_date', 'file', 'reverseFile']);
        $this->kind = 'otro';
        session()->flash('documents_success', $message);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storeUpload($upload): array
    {
        $extension = $upload->getClientOriginalExtension() ?: 'bin';
        $filename = Str::uuid().'.'.$extension;
        $path = $upload->storeAs('drivers/'.$this->driverId, $filename, 'public');

        return [$path, $upload->getClientOriginalName()];
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
