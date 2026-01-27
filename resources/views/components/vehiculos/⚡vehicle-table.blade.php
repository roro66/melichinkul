<?php

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = "";
    public $categoryFilter = "";
    public $statusFilter = "";
    public $sortField = "license_plate";
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
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();
            session()->flash("success", "Vehículo eliminado correctamente.");
        } catch (\Exception $e) {
            session()->flash("error", "Error al eliminar el vehículo: " . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Vehicle::with(["category", "currentDriver"])
            ->where(function ($query) {
                $query->where("license_plate", "like", "%" . $this->search . "%")
                    ->orWhere("brand", "like", "%" . $this->search . "%")
                    ->orWhere("model", "like", "%" . $this->search . "%");
            });

        if ($this->categoryFilter) {
            $query->where("category_id", $this->categoryFilter);
        }

        if ($this->statusFilter) {
            $query->where("status", $this->statusFilter);
        }

        $vehicles = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $categories = VehicleCategory::where("active", true)->orderBy("name")->get();

        return view("livewire.vehiculos.vehicle-table", [
            "vehicles" => $vehicles,
            "categories" => $categories,
        ]);
    }
};
?>
