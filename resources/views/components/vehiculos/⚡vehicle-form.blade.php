<?php

use App\Models\Vehiculo;
use App\Models\CategoriaVehiculo;
use App\Models\Conductor;
use App\Support\ChileanValidationHelper;
use Livewire\Component;

new class extends Component
{
    public $vehiculoId = null;
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
            $vehiculo = Vehiculo::findOrFail($id);
            $this->vehiculoId = $vehiculo->id;
            $this->patente = $vehiculo->patente;
            $this->marca = $vehiculo->marca;
            $this->modelo = $vehiculo->modelo;
            $this->anio = $vehiculo->anio;
            $this->numero_motor = $vehiculo->numero_motor;
            $this->numero_chasis = $vehiculo->numero_chasis;
            $this->categoria_id = $vehiculo->categoria_id;
            $this->tipo_combustible = $vehiculo->tipo_combustible;
            $this->estado = $vehiculo->estado;
            $this->kilometraje_actual = $vehiculo->kilometraje_actual;
            $this->horometro_actual = $vehiculo->horometro_actual;
            $this->conductor_actual_id = $vehiculo->conductor_actual_id;
            $this->fecha_incorporacion = $vehiculo->fecha_incorporacion?->format("Y-m-d");
            $this->valor_compra = $vehiculo->valor_compra;
            $this->observaciones = $vehiculo->observaciones;
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
        $this->rules["patente"][] = "unique:vehiculos,patente," . ($this->vehiculoId ?? "");

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

        if ($this->vehiculoId) {
            $vehiculo = Vehiculo::findOrFail($this->vehiculoId);
            $vehiculo->update($data);
            session()->flash("success", "Vehículo actualizado correctamente.");
        } else {
            Vehiculo::create($data);
            session()->flash("success", "Vehículo creado correctamente.");
        }

        return redirect()->route("vehiculos.index");
    }

    public function render()
    {
        $categorias = CategoriaVehiculo::where("activo", true)->orderBy("nombre")->get();
        $conductores = Conductor::where("activo", true)->orderBy("nombre_completo")->get();

        return <<<BLADE
<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Patente -->
            <div>
                <label for="patente" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Patente <span class="text-red-500">*</span>
                </label>
                <input type="text" id="patente" wire:model.blur="patente" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error(patente) border-red-500 dark:border-red-600 @enderror"
                    placeholder="ABCD12">
                @error(patente)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
                @enderror
            </div>

            <!-- Categoría -->
            <div>
                <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Categoría
                </label>
                <select id="categoria_id" wire:model="categoria_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccionar categoría</option>
                    @foreach(\$categorias as \$categoria)
                        <option value="{{\$categoria->id}}">{{\$categoria->nombre}}</option>
                    @endforeach
                </select>
            </div>

            <!-- Marca -->
            <div>
                <label for="marca" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Marca <span class="text-red-500">*</span>
                </label>
                <input type="text" id="marca" wire:model="marca" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error(marca) border-red-500 dark:border-red-600 @enderror">
                @error(marca)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
                @enderror
            </div>

            <!-- Modelo -->
            <div>
                <label for="modelo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Modelo <span class="text-red-500">*</span>
                </label>
                <input type="text" id="modelo" wire:model="modelo" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error(modelo) border-red-500 dark:border-red-600 @enderror">
                @error(modelo)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
                @enderror
            </div>

            <!-- Año -->
            <div>
                <label for="anio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Año <span class="text-red-500">*</span>
                </label>
                <input type="number" id="anio" wire:model="anio" min="1900" max="2100" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error(anio) border-red-500 dark:border-red-600 @enderror">
                @error(anio)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
                @enderror
            </div>

            <!-- Tipo de Combustible -->
            <div>
                <label for="tipo_combustible" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tipo de Combustible <span class="text-red-500">*</span>
                </label>
                <select id="tipo_combustible" wire:model="tipo_combustible" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="gasolina">Gasolina</option>
                    <option value="diesel">Diesel</option>
                    <option value="gas">Gas</option>
                    <option value="electrico">Eléctrico</option>
                    <option value="hibrido">Híbrido</option>
                </select>
            </div>

            <!-- Estado -->
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select id="estado" wire:model="estado" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                    <option value="mantenimiento">En Mantenimiento</option>
                    <option value="baja">Baja</option>
                </select>
            </div>

            <!-- Fecha de Incorporación -->
            <div>
                <label for="fecha_incorporacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de Incorporación <span class="text-red-500">*</span>
                </label>
                <input type="date" id="fecha_incorporacion" wire:model="fecha_incorporacion" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error(fecha_incorporacion) border-red-500 dark:border-red-600 @enderror">
                @error(fecha_incorporacion)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ \$message }}</p>
                @enderror
            </div>

            <!-- Conductor Actual -->
            <div>
                <label for="conductor_actual_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Conductor Actual
                </label>
                <select id="conductor_actual_id" wire:model="conductor_actual_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sin asignar</option>
                    @foreach(\$conductores as \$conductor)
                        <option value="{{\$conductor->id}}">{{\$conductor->nombre_completo}} ({{ \$conductor->rut }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Número de Motor -->
            <div>
                <label for="numero_motor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Número de Motor
                </label>
                <input type="text" id="numero_motor" wire:model="numero_motor" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Número de Chasis -->
            <div>
                <label for="numero_chasis" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Número de Chasis
                </label>
                <input type="text" id="numero_chasis" wire:model="numero_chasis" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Kilometraje Actual -->
            <div>
                <label for="kilometraje_actual" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Kilometraje Actual
                </label>
                <input type="number" id="kilometraje_actual" wire:model="kilometraje_actual" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Horómetro Actual -->
            <div>
                <label for="horometro_actual" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horómetro Actual
                </label>
                <input type="number" id="horometro_actual" wire:model="horometro_actual" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Valor de Compra -->
            <div>
                <label for="valor_compra" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Valor de Compra
                </label>
                <input type="number" id="valor_compra" wire:model="valor_compra" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Observaciones -->
        <div>
            <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Observaciones
            </label>
            <textarea id="observaciones" wire:model="observaciones" rows="3" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{route(vehiculos.index)}}" 
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                Cancelar
            </a>
            <button type="submit" 
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
                {{ \$vehiculoId ? "Actualizar" : "Crear" }} Vehículo
            </button>
        </div>
    </form>
</div>
BLADE;
    }
};
?>
