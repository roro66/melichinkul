<?php

use App\Models\Vehiculo;
use App\Models\CategoriaVehiculo;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = "";
    public $categoryFilter = "";
    public $statusFilter = "";
    public $sortField = "patente";
    public $sortDirection = "asc";

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
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
        $vehicle = Vehiculo::findOrFail($id);
        $vehicle->delete();
        session()->flash("success", "Vehículo eliminado correctamente.");
    }

    public function render()
    {
        $query = Vehiculo::with(["categoria", "conductorActual"])
            ->where(function ($query) {
                $query->where("patente", "like", "%" . $this->search . "%")
                    ->orWhere("marca", "like", "%" . $this->search . "%")
                    ->orWhere("modelo", "like", "%" . $this->search . "%");
            });

        if ($this->categoryFilter) {
            $query->where("categoria_id", $this->categoryFilter);
        }

        if ($this->statusFilter) {
            $query->where("estado", $this->statusFilter);
        }

        $vehicles = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $categories = CategoriaVehiculo::where("activo", true)->orderBy("nombre")->get();

        return view("livewire.vehiculos.vehicle-table", [
            "vehicles" => $vehicles,
            "categories" => $categories,
        ]);
    }
};
?>
