<?php

use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Driver;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public $maintenanceId = null;
    public $vehicle_id = "";
    public $type = "preventive";
    public $status = "scheduled";
    public $scheduled_date = "";
    public $start_date = "";
    public $end_date = "";
    public $mileage_at_maintenance = "";
    public $hours_at_maintenance = "";
    public $entry_reason = "";
    public $work_description = "";
    public $work_performed = "";
    public $parts_cost = 0;
    public $labor_cost = 0;
    public $total_cost = 0;
    public $hours_worked = "";
    public $workshop_supplier = "";
    public $responsible_technician_id = "";
    public $assigned_driver_id = "";
    public $observations = "";
    public $evidence_invoice = null;
    public $evidence_photo = null;

    protected $rules = [
        "vehicle_id" => ["required", "exists:vehicles,id"],
        "type" => ["required", "string", "in:preventive,corrective,inspection"],
        "status" => ["required", "string", "in:scheduled,in_progress,completed,pending_approval,cancelled"],
        "scheduled_date" => ["required", "date"],
        "start_date" => ["nullable", "date"],
        "end_date" => ["nullable", "date"],
        "mileage_at_maintenance" => ["nullable", "numeric", "min:0"],
        "hours_at_maintenance" => ["nullable", "numeric", "min:0"],
        "entry_reason" => ["nullable", "string"],
        "work_description" => ["required", "string"],
        "work_performed" => ["nullable", "string"],
        "parts_cost" => ["nullable", "integer", "min:0"],
        "labor_cost" => ["nullable", "integer", "min:0"],
        "total_cost" => ["nullable", "integer", "min:0"],
        "hours_worked" => ["nullable", "numeric", "min:0"],
        "workshop_supplier" => ["nullable", "string", "max:255"],
        "responsible_technician_id" => ["nullable", "exists:users,id"],
        "assigned_driver_id" => ["nullable", "exists:drivers,id"],
        "observations" => ["nullable", "string"],
        "evidence_invoice" => ["nullable", "file", "mimes:pdf,jpg,jpeg,png", "max:10240"],
        "evidence_photo" => ["nullable", "file", "mimes:pdf,jpg,jpeg,png", "max:10240"],
    ];

    protected $messages = [
        "vehicle_id.required" => "El vehículo es obligatorio.",
        "work_description.required" => "La descripción del trabajo es obligatoria.",
        "scheduled_date.required" => "La fecha programada es obligatoria.",
        "evidence_invoice.max" => "El documento no debe superar 10 MB.",
        "evidence_photo.max" => "La foto no debe superar 10 MB.",
    ];

    public function mount($id = null)
    {
        if ($id) {
            $maintenance = Maintenance::findOrFail($id);
            $this->maintenanceId = $maintenance->id;
            $this->vehicle_id = $maintenance->vehicle_id;
            $this->type = $maintenance->type;
            $this->status = $maintenance->status;
            $this->scheduled_date = $maintenance->scheduled_date?->format("Y-m-d");
            $this->start_date = $maintenance->start_date?->format("Y-m-d");
            $this->end_date = $maintenance->end_date?->format("Y-m-d");
            $this->mileage_at_maintenance = $maintenance->mileage_at_maintenance;
            $this->hours_at_maintenance = $maintenance->hours_at_maintenance;
            $this->entry_reason = $maintenance->entry_reason;
            $this->work_description = $maintenance->work_description;
            $this->work_performed = $maintenance->work_performed;
            $this->parts_cost = $maintenance->parts_cost;
            $this->labor_cost = $maintenance->labor_cost;
            $this->total_cost = $maintenance->total_cost;
            $this->hours_worked = $maintenance->hours_worked;
            $this->workshop_supplier = $maintenance->workshop_supplier;
            $this->responsible_technician_id = $maintenance->responsible_technician_id;
            $this->assigned_driver_id = $maintenance->assigned_driver_id;
            $this->observations = $maintenance->observations;
        }
    }

    public function updatedPartsCost()
    {
        $this->calculateTotalCost();
    }

    public function updatedLaborCost()
    {
        $this->calculateTotalCost();
    }

    public function calculateTotalCost()
    {
        $this->total_cost = ($this->parts_cost ?? 0) + ($this->labor_cost ?? 0);
    }

    public function save()
    {
        $this->validate();

        $status = $this->status;
        $totalCost = (int) ($this->total_cost ?? 0);
        $threshold = (int) config('maintenance.approval_threshold', 500_000);
        if ($status === 'completed' && $totalCost > $threshold) {
            $status = 'pending_approval';
        }

        $data = [
            "vehicle_id" => $this->vehicle_id,
            "type" => $this->type,
            "status" => $status,
            "scheduled_date" => $this->scheduled_date,
            "start_date" => $this->start_date ?: null,
            "end_date" => $this->end_date ?: null,
            "mileage_at_maintenance" => $this->mileage_at_maintenance ?: null,
            "hours_at_maintenance" => $this->hours_at_maintenance ?: null,
            "entry_reason" => $this->entry_reason ?: null,
            "work_description" => $this->work_description,
            "work_performed" => $this->work_performed ?: null,
            "parts_cost" => $this->parts_cost ?: 0,
            "labor_cost" => $this->labor_cost ?: 0,
            "total_cost" => $this->total_cost ?: 0,
            "hours_worked" => $this->hours_worked ?: null,
            "workshop_supplier" => $this->workshop_supplier ?: null,
            "responsible_technician_id" => $this->responsible_technician_id ?: null,
            "assigned_driver_id" => $this->assigned_driver_id ?: null,
            "observations" => $this->observations ?: null,
        ];

        if ($this->maintenanceId) {
            $maintenance = Maintenance::findOrFail($this->maintenanceId);
            $maintenance->update($data);
            if ($status === 'completed') {
                $maintenance->processSparePartsUsage();
            }
            session()->flash("success", $status === 'pending_approval'
                ? "Mantenimiento actualizado. El costo supera el umbral de aprobación; quedó pendiente de aprobación."
                : "Mantenimiento actualizado correctamente.");
        } else {
            $maintenance = Maintenance::create($data);
            if ($status === 'completed') {
                $maintenance->processSparePartsUsage();
            }
            session()->flash("success", $status === 'pending_approval'
                ? "Mantenimiento creado. El costo supera el umbral de aprobación; quedó pendiente de aprobación."
                : "Mantenimiento creado correctamente.");
        }

        $evidenceData = [];
        if ($this->evidence_invoice) {
            if ($maintenance->evidence_invoice_path && Storage::disk("public")->exists($maintenance->evidence_invoice_path)) {
                Storage::disk("public")->delete($maintenance->evidence_invoice_path);
            }
            $evidenceData["evidence_invoice_path"] = $this->evidence_invoice->store("maintenances/" . $maintenance->id, "public");
        }
        if ($this->evidence_photo) {
            if ($maintenance->evidence_photo_path && Storage::disk("public")->exists($maintenance->evidence_photo_path)) {
                Storage::disk("public")->delete($maintenance->evidence_photo_path);
            }
            $evidenceData["evidence_photo_path"] = $this->evidence_photo->store("maintenances/" . $maintenance->id, "public");
        }
        if (! empty($evidenceData)) {
            $maintenance->update($evidenceData);
        }

        return redirect()->route("mantenimientos.index");
    }

    public function render()
    {
        $vehicles = Vehicle::where("status", "!=", "decommissioned")->orderBy("license_plate")->get();
        $technicians = User::role(['technician', 'administrator'])->orderBy('name')->get();
        $drivers = Driver::where("active", true)->orderBy("full_name")->get();
        $maintenance = $this->maintenanceId ? Maintenance::find($this->maintenanceId) : null;

        return view("livewire.mantenimientos.maintenance-form", [
            "vehicles" => $vehicles,
            "technicians" => $technicians,
            "drivers" => $drivers,
            "maintenance" => $maintenance,
        ]);
    }
};
?>
