<?php

use App\Models\Maintenance;
use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = "";
    public $vehicleFilter = "";
    public $typeFilter = "";
    public $statusFilter = "";
    public $sortField = "scheduled_date";
    public $sortDirection = "desc";

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingVehicleFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === "asc" ? "desc" : "asc";
        } else {
            $this->sortField = $field;
            $this->sortDirection = "asc";
        }
    }

    public function delete($id)
    {
        try {
            $maintenance = Maintenance::findOrFail($id);
            $maintenance->delete();
            session()->flash("success", "Mantenimiento eliminado correctamente.");
        } catch (\Exception $e) {
            session()->flash("error", "Error al eliminar el mantenimiento: " . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Maintenance::with(["vehicle", "responsibleTechnician", "assignedDriver"])
            ->where(function ($query) {
                $query->whereHas("vehicle", function ($vehicleQuery) {
                    $vehicleQuery->where("license_plate", "like", "%" . $this->search . "%")
                        ->orWhere("brand", "like", "%" . $this->search . "%")
                        ->orWhere("model", "like", "%" . $this->search . "%");
                })
                ->orWhere("work_description", "like", "%" . $this->search . "%")
                ->orWhere("entry_reason", "like", "%" . $this->search . "%");
            });

        if ($this->vehicleFilter) {
            $query->where("vehicle_id", $this->vehicleFilter);
        }

        if ($this->typeFilter) {
            $query->where("type", $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where("status", $this->statusFilter);
        }

        $maintenances = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $vehicles = Vehicle::where("status", "!=", "decommissioned")->orderBy("license_plate")->get();

        return view("livewire.mantenimientos.maintenance-table", [
            "maintenances" => $maintenances,
            "vehicles" => $vehicles,
        ]);
    }
};
?>
