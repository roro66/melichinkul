<?php

use App\Models\Vehiculo;
use App\Models\CategoriaVehiculo;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = "";
    public $categoriaFilter = "";
    public $estadoFilter = "";
    public $sortField = "patente";
    public $sortDirection = "asc";

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoriaFilter()
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
        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->delete();
        session()->flash("success", "Vehículo eliminado correctamente.");
    }

    public function render()
    {
        $query = Vehiculo::with(["categoria", "conductorActual"])
            ->where(function ($q) {
                $q->where("patente", "like", "%" . $this->search . "%")
                    ->orWhere("marca", "like", "%" . $this->search . "%")
                    ->orWhere("modelo", "like", "%" . $this->search . "%");
            });

        if ($this->categoriaFilter) {
            $query->where("categoria_id", $this->categoriaFilter);
        }

        if ($this->estadoFilter) {
            $query->where("estado", $this->estadoFilter);
        }

        $vehiculos = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $categorias = CategoriaVehiculo::where("activo", true)->orderBy("nombre")->get();

        return view("livewire.vehiculos.vehicle-table", [
            "vehiculos" => $vehiculos,
            "categorias" => $categorias,
        ]);
    }
};
?>
