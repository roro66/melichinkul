<?php

use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Services\AssignmentService;
use App\Support\ChileanValidationHelper;
use Livewire\Component;

new class extends Component
{
    public $vehicleId = null;
    public $license_plate = "";
    public $brand = "";
    public $model = "";
    public $year = "";
    public $engine_number = "";
    public $chassis_number = "";
    public $category_id = "";
    public $fuel_type = "gasoline";
    public $status = "active";
    public $current_mileage = 0;
    public $current_hours = 0;
    public $current_driver_id = "";
    public $incorporation_date = "";
    public $purchase_value = "";
    public $observations = "";

    protected $rules = [
        "license_plate" => ["required", "string", "max:255"],
        "brand" => ["required", "string", "max:255"],
        "model" => ["required", "string", "max:255"],
        "year" => ["required", "integer", "min:1900", "max:2100"],
        "engine_number" => ["nullable", "string", "max:255"],
        "chassis_number" => ["nullable", "string", "max:255"],
        "category_id" => ["nullable", "exists:vehicle_categories,id"],
        "fuel_type" => ["required", "string", "in:gasoline,diesel,electric,hybrid,gnv"],
        "status" => ["required", "string", "in:active,inactive,maintenance,decommissioned"],
        "current_mileage" => ["nullable", "numeric", "min:0"],
        "current_hours" => ["nullable", "numeric", "min:0"],
        "current_driver_id" => ["nullable", "exists:drivers,id"],
        "incorporation_date" => ["required", "date"],
        "purchase_value" => ["nullable", "integer", "min:0"],
        "observations" => ["nullable", "string"],
    ];

    protected $messages = [
        "license_plate.required" => "La patente es obligatoria.",
        "license_plate.unique" => "Esta patente ya está registrada.",
        "brand.required" => "La marca es obligatoria.",
        "model.required" => "El modelo es obligatorio.",
        "year.required" => "El año es obligatorio.",
        "incorporation_date.required" => "La fecha de incorporación es obligatoria.",
    ];

    public function mount($id = null)
    {
        if ($id) {
            $vehicle = Vehicle::findOrFail($id);
            $this->vehicleId = $vehicle->id;
            $this->license_plate = $vehicle->license_plate;
            $this->brand = $vehicle->brand;
            $this->model = $vehicle->model;
            $this->year = $vehicle->year;
            $this->engine_number = $vehicle->engine_number;
            $this->chassis_number = $vehicle->chassis_number;
            $this->category_id = $vehicle->category_id;
            $this->fuel_type = $vehicle->fuel_type;
            $this->status = $vehicle->status;
            $this->current_mileage = $vehicle->current_mileage;
            $this->current_hours = $vehicle->current_hours;
            $this->current_driver_id = $vehicle->current_driver_id;
            $this->incorporation_date = $vehicle->incorporation_date?->format("Y-m-d");
            $this->purchase_value = $vehicle->purchase_value;
            $this->observations = $vehicle->observations;
        }
    }

    public function updatedLicensePlate($value)
    {
        $this->license_plate = strtoupper($value);
    }

    public function save()
    {
        // Validar patente chilena
        $this->rules["license_plate"][] = function ($attribute, $value, $fail) {
            if (!ChileanValidationHelper::validarPatente($value)) {
                $fail(__("chile.patente", ["attribute" => "license_plate"]));
            }
        };

        // Validar unicidad de patente (excepto si es el mismo vehículo)
        $this->rules["license_plate"][] = "unique:vehicles,license_plate," . ($this->vehicleId ?? "");

        $this->validate();

        $driverId = $this->current_driver_id ? (int) $this->current_driver_id : null;
        $data = [
            "license_plate" => strtoupper($this->license_plate),
            "brand" => $this->brand,
            "model" => $this->model,
            "year" => $this->year,
            "engine_number" => $this->engine_number ?: null,
            "chassis_number" => $this->chassis_number ?: null,
            "category_id" => $this->category_id ?: null,
            "fuel_type" => $this->fuel_type,
            "status" => $this->status,
            "current_mileage" => $this->current_mileage ?: 0,
            "current_hours" => $this->current_hours ?: 0,
            "incorporation_date" => $this->incorporation_date,
            "purchase_value" => $this->purchase_value ?: null,
            "observations" => $this->observations ?: null,
        ];

        $assignmentService = app(AssignmentService::class);

        if ($this->vehicleId) {
            $vehicle = Vehicle::findOrFail($this->vehicleId);
            $previousDriverId = $vehicle->current_driver_id;

            if ($driverId && $driverId !== $previousDriverId) {
                $assignmentService->assign(
                    Driver::findOrFail($driverId),
                    $vehicle,
                    auth()->user()
                );
            } elseif (! $driverId && $previousDriverId) {
                $active = DriverAssignment::where('vehicle_id', $vehicle->id)
                    ->whereNull('end_date')
                    ->first();
                if ($active) {
                    $active->update(['end_date' => now()->toDateString(), 'end_reason' => 'driver_change']);
                }
                $vehicle->update(['current_driver_id' => null]);
            }

            $vehicle->update($data);
            session()->flash("success", "Vehículo actualizado correctamente.");
        } else {
            $vehicle = Vehicle::create(array_merge($data, ['current_driver_id' => null]));
            if ($driverId) {
                $assignmentService->assign(
                    Driver::findOrFail($driverId),
                    $vehicle,
                    auth()->user()
                );
            }
            session()->flash("success", "Vehículo creado correctamente.");
        }

        return redirect()->route("vehiculos.index");
    }

    public function render()
    {
        $categories = VehicleCategory::where("active", true)->orderBy("name")->get();
        $drivers = Driver::where("active", true)->orderBy("full_name")->get();

        return view("livewire.vehiculos.vehicle-form", [
            "categories" => $categories,
            "drivers" => $drivers,
        ]);
    }
};
?>
