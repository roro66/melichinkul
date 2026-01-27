<?php

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use App\Models\User;
use App\Models\Conductor;
use Livewire\Component;

new class extends Component
{
    public $maintenanceId = null;
    public $vehiculo_id = "";
    public $tipo = "preventivo";
    public $estado = "programado";
    public $fecha_programada = "";
    public $fecha_inicio = "";
    public $fecha_fin = "";
    public $kilometraje_en_mantenimiento = "";
    public $horometro_en_mantenimiento = "";
    public $motivo_ingreso = "";
    public $descripcion_trabajo = "";
    public $trabajos_realizados = "";
    public $costo_repuestos = 0;
    public $costo_mano_obra = 0;
    public $costo_total = 0;
    public $horas_trabajadas = "";
    public $taller_proveedor = "";
    public $tecnico_responsable_id = "";
    public $conductor_asignado_id = "";
    public $observaciones = "";

    protected $rules = [
        "vehiculo_id" => ["required", "exists:vehiculos,id"],
        "tipo" => ["required", "string", "in:preventivo,correctivo,inspeccion"],
        "estado" => ["required", "string", "in:programado,en_proceso,completado,cancelado"],
        "fecha_programada" => ["required", "date"],
        "fecha_inicio" => ["nullable", "date"],
        "fecha_fin" => ["nullable", "date"],
        "kilometraje_en_mantenimiento" => ["nullable", "numeric", "min:0"],
        "horometro_en_mantenimiento" => ["nullable", "numeric", "min:0"],
        "motivo_ingreso" => ["nullable", "string"],
        "descripcion_trabajo" => ["required", "string"],
        "trabajos_realizados" => ["nullable", "string"],
        "costo_repuestos" => ["nullable", "integer", "min:0"],
        "costo_mano_obra" => ["nullable", "integer", "min:0"],
        "costo_total" => ["nullable", "integer", "min:0"],
        "horas_trabajadas" => ["nullable", "numeric", "min:0"],
        "taller_proveedor" => ["nullable", "string", "max:255"],
        "tecnico_responsable_id" => ["nullable", "exists:users,id"],
        "conductor_asignado_id" => ["nullable", "exists:conductores,id"],
        "observaciones" => ["nullable", "string"],
    ];

    protected $messages = [
        "vehiculo_id.required" => "El vehículo es obligatorio.",
        "descripcion_trabajo.required" => "La descripción del trabajo es obligatoria.",
        "fecha_programada.required" => "La fecha programada es obligatoria.",
    ];

    public function mount($id = null)
    {
        if ($id) {
            $maintenance = Mantenimiento::findOrFail($id);
            $this->maintenanceId = $maintenance->id;
            $this->vehiculo_id = $maintenance->vehiculo_id;
            $this->tipo = $maintenance->tipo;
            $this->estado = $maintenance->estado;
            $this->fecha_programada = $maintenance->fecha_programada?->format("Y-m-d");
            $this->fecha_inicio = $maintenance->fecha_inicio?->format("Y-m-d");
            $this->fecha_fin = $maintenance->fecha_fin?->format("Y-m-d");
            $this->kilometraje_en_mantenimiento = $maintenance->kilometraje_en_mantenimiento;
            $this->horometro_en_mantenimiento = $maintenance->horometro_en_mantenimiento;
            $this->motivo_ingreso = $maintenance->motivo_ingreso;
            $this->descripcion_trabajo = $maintenance->descripcion_trabajo;
            $this->trabajos_realizados = $maintenance->trabajos_realizados;
            $this->costo_repuestos = $maintenance->costo_repuestos;
            $this->costo_mano_obra = $maintenance->costo_mano_obra;
            $this->costo_total = $maintenance->costo_total;
            $this->horas_trabajadas = $maintenance->horas_trabajadas;
            $this->taller_proveedor = $maintenance->taller_proveedor;
            $this->tecnico_responsable_id = $maintenance->tecnico_responsable_id;
            $this->conductor_asignado_id = $maintenance->conductor_asignado_id;
            $this->observaciones = $maintenance->observaciones;
        }
    }

    public function updatedCostoRepuestos()
    {
        $this->calculateTotalCost();
    }

    public function updatedCostoManoObra()
    {
        $this->calculateTotalCost();
    }

    public function calculateTotalCost()
    {
        $this->costo_total = ($this->costo_repuestos ?? 0) + ($this->costo_mano_obra ?? 0);
    }

    public function save()
    {
        $this->validate();

        $data = [
            "vehiculo_id" => $this->vehiculo_id,
            "tipo" => $this->tipo,
            "estado" => $this->estado,
            "fecha_programada" => $this->fecha_programada,
            "fecha_inicio" => $this->fecha_inicio ?: null,
            "fecha_fin" => $this->fecha_fin ?: null,
            "kilometraje_en_mantenimiento" => $this->kilometraje_en_mantenimiento ?: null,
            "horometro_en_mantenimiento" => $this->horometro_en_mantenimiento ?: null,
            "motivo_ingreso" => $this->motivo_ingreso ?: null,
            "descripcion_trabajo" => $this->descripcion_trabajo,
            "trabajos_realizados" => $this->trabajos_realizados ?: null,
            "costo_repuestos" => $this->costo_repuestos ?: 0,
            "costo_mano_obra" => $this->costo_mano_obra ?: 0,
            "costo_total" => $this->costo_total ?: 0,
            "horas_trabajadas" => $this->horas_trabajadas ?: null,
            "taller_proveedor" => $this->taller_proveedor ?: null,
            "tecnico_responsable_id" => $this->tecnico_responsable_id ?: null,
            "conductor_asignado_id" => $this->conductor_asignado_id ?: null,
            "observaciones" => $this->observaciones ?: null,
        ];

        if ($this->maintenanceId) {
            $maintenance = Mantenimiento::findOrFail($this->maintenanceId);
            $maintenance->update($data);
            session()->flash("success", "Mantenimiento actualizado correctamente.");
        } else {
            Mantenimiento::create($data);
            session()->flash("success", "Mantenimiento creado correctamente.");
        }

        return redirect()->route("mantenimientos.index");
    }

    public function render()
    {
        $vehicles = Vehiculo::where("estado", "!=", "baja")->orderBy("patente")->get();
        $technicians = User::where("rol", "tecnico")->orWhere("rol", "administrador")->orderBy("name")->get();
        $drivers = Conductor::where("activo", true)->orderBy("nombre_completo")->get();

        return view("livewire.mantenimientos.maintenance-form", [
            "vehicles" => $vehicles,
            "technicians" => $technicians,
            "drivers" => $drivers,
        ]);
    }
};
?>
