<?php

namespace App\Livewire\Conductores;

use App\Models\Driver;
use App\Support\ChileanValidationHelper;
use Livewire\Component;

class DriverForm extends Component
{
    public ?int $driverId = null;

    public string $rut = '';

    public string $full_name = '';

    public string $phone = '';

    public string $email = '';

    public string $license_number = '';

    public string $license_class = '';

    public string $license_issue_date = '';

    public string $license_expiration_date = '';

    public bool $active = true;

    public string $observations = '';

    protected function rules(): array
    {
        $rules = [
            'rut' => ['required', 'string', 'max:20'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'license_class' => ['nullable', 'string', 'max:20'],
            'license_issue_date' => ['nullable', 'date'],
            'license_expiration_date' => ['nullable', 'date', 'after_or_equal:license_issue_date'],
            'active' => ['boolean'],
            'observations' => ['nullable', 'string'],
        ];

        if ($this->driverId) {
            $rules['rut'][] = 'unique:drivers,rut,' . $this->driverId;
        } else {
            $rules['rut'][] = 'unique:drivers,rut';
        }

        return $rules;
    }

    protected $messages = [
        'rut.required' => 'El RUT es obligatorio.',
        'rut.unique' => 'Este RUT ya está registrado.',
        'full_name.required' => 'El nombre completo es obligatorio.',
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $driver = Driver::findOrFail($id);
            $this->driverId = $driver->id;
            $this->rut = $driver->rut ?? '';
            $this->full_name = $driver->full_name ?? '';
            $this->phone = $driver->phone ?? '';
            $this->email = $driver->email ?? '';
            $this->license_number = $driver->license_number ?? '';
            $this->license_class = $driver->license_class ?? '';
            $this->license_issue_date = $driver->license_issue_date?->format('Y-m-d') ?? '';
            $this->license_expiration_date = $driver->license_expiration_date?->format('Y-m-d') ?? '';
            $this->active = $driver->active ?? true;
            $this->observations = $driver->observations ?? '';
        }
    }

    public function updatedRut($value): void
    {
        $this->rut = ChileanValidationHelper::normalizarRut($value);
    }

    public function save(): mixed
    {
        $this->rules['rut'][] = function ($attribute, $value, $fail) {
            if (! ChileanValidationHelper::validarRut($value)) {
                $fail(__('chile.rut', ['attribute' => 'RUT']));
            }
        };
        $this->validate();

        $data = [
            'rut' => ChileanValidationHelper::normalizarRut($this->rut),
            'full_name' => $this->full_name,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'license_number' => $this->license_number ?: null,
            'license_class' => $this->license_class ?: null,
            'license_issue_date' => $this->license_issue_date ?: null,
            'license_expiration_date' => $this->license_expiration_date ?: null,
            'active' => $this->active,
            'observations' => $this->observations ?: null,
        ];

        if ($this->driverId) {
            Driver::findOrFail($this->driverId)->update($data);
            session()->flash('success', 'Conductor actualizado correctamente.');
        } else {
            Driver::create($data);
            session()->flash('success', 'Conductor creado correctamente.');
        }

        return redirect()->route('conductores.index');
    }

    public function render()
    {
        return view('livewire.conductores.driver-form');
    }
}
