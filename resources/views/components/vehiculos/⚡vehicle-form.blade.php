<?php

use App\Models\Vehiculo;
use App\Models\CategoriaVehiculo;
use App\Models\Conductor;
use App\Support\ChileanValidationHelper;
use Livewire\Component;

new class extends Component
{
    public $vehicleId = null;
    public $patente = "";
    public $marca = "";
    public $modelo = "";
    public $anio = "";
    public $numero_motor = "";
    public $numero_chasis = "";
    public $categoria_id = "";
    public $tipo_combustible = "gasolina";
    public $estado = "activo";
    public $kilometraje_actual = 0;
    public $horometro_actual = 0;
    public $conductor_actual_id = "";
    public $fecha_incorporacion = "";
    public $valor_compra = "";
    public $observaciones = "";

    protected $rules = [
        "patente" => ["required", "string", "max:255"],
        "marca" => ["required", "string", "max:255"],
        "modelo" => ["required", "string", "max:255"],
        "anio" => ["required", "integer", "min:1900", "max:2100"],
        "numero_motor" => ["nullable", "string", "max:255"],
        "numero_chasis" => ["nullable", "string", "max:255"],
        "categoria_id" => ["nullable", "exists:categorias_vehiculos,id"],
        "tipo_combustible" => ["required", "string", "in:gasolina,diesel,gas,electrico,hibrido"],
        "estado" => ["required", "string", "in:activo,inactivo,mantenimiento,baja"],
        "kilometraje_actual" => ["nullable", "numeric", "min:0"],
        "horometro_actual" => ["nullable", "numeric", "min:0"],
        "conductor_actual_id" => ["nullable", "exists:conductores,id"],
        "fecha_incorporacion" => ["required", "date"],
        "valor_compra" => ["nullable", "integer", "min:0"],
        "observaciones" => ["nullable", "string"],
    ];

    protected $messages = [
        "patente.required" => "La patente es obligatoria.",
        "patente.unique" => "Esta patente ya está registrada.",
        "marca.required" => "La marca es obligatoria.",
        "modelo.required" => "El modelo es obligatorio.",
        "anio.required" => "El año es obligatorio.",
        "fecha_incorporacion.required" => "La fecha de incorporación es obligatoria.",
    ];

    public function mount($id = null)
    {
        if ($id) {
            $vehicle = Vehiculo::findOrFail($id);
            $this->vehicleId = $vehicle->id;
            $this->patente = $vehicle->patente;
            $this->marca = $vehicle->marca;
            $this->modelo = $vehicle->modelo;
            $this->anio = $vehicle->anio;
            $this->numero_motor = $vehicle->numero_motor;
            $this->numero_chasis = $vehicle->numero_chasis;
            $this->categoria_id = $vehicle->categoria_id;
            $this->tipo_combustible = $vehicle->tipo_combustible;
            $this->estado = $vehicle->estado;
            $this->kilometraje_actual = $vehicle->kilometraje_actual;
            $this->horometro_actual = $vehicle->horometro_actual;
            $this->conductor_actual_id = $vehicle->conductor_actual_id;
            $this->fecha_incorporacion = $vehicle->fecha_incorporacion?->format("Y-m-d");
            $this->valor_compra = $vehicle->valor_compra;
            $this->observaciones = $vehicle->observaciones;
        }
    }

    public function updatedPatente($value)
    {
        $this->patente = strtoupper($value);
    }

    public function save()
    {
        // Validar patente chilena
        $this->rules["patente"][] = function ($attribute, $value, $fail) {
            if (!ChileanValidationHelper::validarPatente($value)) {
                $fail(__("chile.patente", ["attribute" => "patente"]));
            }
        };

        // Validar unicidad de patente (excepto si es el mismo vehículo)
        $this->rules["patente"][] = "unique:vehiculos,patente," . ($this->vehicleId ?? "");

        $this->validate();

        $data = [
            "patente" => strtoupper($this->patente),
            "marca" => $this->marca,
            "modelo" => $this->modelo,
            "anio" => $this->anio,
            "numero_motor" => $this->numero_motor ?: null,
            "numero_chasis" => $this->numero_chasis ?: null,
            "categoria_id" => $this->categoria_id ?: null,
            "tipo_combustible" => $this->tipo_combustible,
            "estado" => $this->estado,
            "kilometraje_actual" => $this->kilometraje_actual ?: 0,
            "horometro_actual" => $this->horometro_actual ?: 0,
            "conductor_actual_id" => $this->conductor_actual_id ?: null,
            "fecha_incorporacion" => $this->fecha_incorporacion,
            "valor_compra" => $this->valor_compra ?: null,
            "observaciones" => $this->observaciones ?: null,
        ];

        if ($this->vehicleId) {
            $vehicle = Vehiculo::findOrFail($this->vehicleId);
            $vehicle->update($data);
            session()->flash("success", "Vehículo actualizado correctamente.");
        } else {
            Vehiculo::create($data);
            session()->flash("success", "Vehículo creado correctamente.");
        }

        return redirect()->route("vehiculos.index");
    }

    public function render()
    {
        $categories = CategoriaVehiculo::where("activo", true)->orderBy("nombre")->get();
        $drivers = Conductor::where("activo", true)->orderBy("nombre_completo")->get();

        return view("livewire.vehiculos.vehicle-form", [
            "categories" => $categories,
            "drivers" => $drivers,
        ]);
    }
};
?>
