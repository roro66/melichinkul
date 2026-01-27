<?php

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = "";
    public $vehicleFilter = "";
    public $typeFilter = "";
    public $statusFilter = "";
    public $sortField = "fecha_programada";
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
        $maintenance = Mantenimiento::findOrFail($id);
        $maintenance->delete();
        session()->flash("success", "Mantenimiento eliminado correctamente.");
    }

    public function render()
    {
        $query = Mantenimiento::with(["vehiculo", "tecnicoResponsable", "conductorAsignado"])
            ->where(function ($query) {
                $query->whereHas("vehiculo", function ($vehicleQuery) {
                    $vehicleQuery->where("patente", "like", "%" . $this->search . "%")
                        ->orWhere("marca", "like", "%" . $this->search . "%")
                        ->orWhere("modelo", "like", "%" . $this->search . "%");
                })
                ->orWhere("descripcion_trabajo", "like", "%" . $this->search . "%")
                ->orWhere("motivo_ingreso", "like", "%" . $this->search . "%");
            });

        if ($this->vehicleFilter) {
            $query->where("vehiculo_id", $this->vehicleFilter);
        }

        if ($this->typeFilter) {
            $query->where("tipo", $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where("estado", $this->statusFilter);
        }

        $maintenances = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $vehicles = Vehiculo::where("estado", "!=", "baja")->orderBy("patente")->get();

        return view("livewire.mantenimientos.maintenance-table", [
            "maintenances" => $maintenances,
            "vehicles" => $vehicles,
        ]);
    }
};
?>
