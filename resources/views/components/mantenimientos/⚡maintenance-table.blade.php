<?php

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = "";
    public $vehiculoFilter = "";
    public $tipoFilter = "";
    public $estadoFilter = "";
    public $sortField = "fecha_programada";
    public $sortDirection = "desc";

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingVehiculoFilter()
    {
        $this->resetPage();
    }

    public function updatingTipoFilter()
    {
        $this->resetPage();
    }

    public function updatingEstadoFilter()
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
        $mantenimiento = Mantenimiento::findOrFail($id);
        $mantenimiento->delete();
        session()->flash("success", "Mantenimiento eliminado correctamente.");
    }

    public function render()
    {
        $query = Mantenimiento::with(["vehiculo", "tecnicoResponsable", "conductorAsignado"])
            ->where(function ($q) {
                $q->whereHas("vehiculo", function ($vq) {
                    $vq->where("patente", "like", "%" . $this->search . "%")
                        ->orWhere("marca", "like", "%" . $this->search . "%")
                        ->orWhere("modelo", "like", "%" . $this->search . "%");
                })
                ->orWhere("descripcion_trabajo", "like", "%" . $this->search . "%")
                ->orWhere("motivo_ingreso", "like", "%" . $this->search . "%");
            });

        if ($this->vehiculoFilter) {
            $query->where("vehiculo_id", $this->vehiculoFilter);
        }

        if ($this->tipoFilter) {
            $query->where("tipo", $this->tipoFilter);
        }

        if ($this->estadoFilter) {
            $query->where("estado", $this->estadoFilter);
        }

        $mantenimientos = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $vehiculos = Vehiculo::where("estado", "!=", "baja")->orderBy("patente")->get();

        return view("livewire.mantenimientos.maintenance-table", [
            "mantenimientos" => $mantenimientos,
            "vehiculos" => $vehiculos,
        ]);
    }
};
?>
