<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form wire:submit="save" class="space-y-6">
        <!-- Información Básica -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="vehiculo_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Vehículo <span class="text-red-500">*</span>
                </label>
                <select id="vehiculo_id" wire:model="vehiculo_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error(vehiculo_id) border-red-500 dark:border-red-600 @enderror">
                    <option value="">Seleccionar vehículo</option>
                    @foreach($vehiculos as $vehiculo)
                        <option value="{{$vehiculo->id}}">{{$vehiculo->patente}} - {{$vehiculo->marca}} {{$vehiculo->modelo}}</option>
                    @endforeach
                </select>
                @error(vehiculo_id)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tipo <span class="text-red-500">*</span>
                </label>
                <select id="tipo" wire:model="tipo" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="preventivo">Preventivo</option>
                    <option value="correctivo">Correctivo</option>
                    <option value="inspeccion">Inspección</option>
                </select>
            </div>

            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select id="estado" wire:model="estado" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="programado">Programado</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="fecha_programada" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha Programada <span class="text-red-500">*</span>
                </label>
                <input type="date" id="fecha_programada" wire:model="fecha_programada" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error(fecha_programada) border-red-500 dark:border-red-600 @enderror">
                @error(fecha_programada)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de Inicio
                </label>
                <input type="date" id="fecha_inicio" wire:model="fecha_inicio" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de Fin
                </label>
                <input type="date" id="fecha_fin" wire:model="fecha_fin" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Kilometraje y Horómetro -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="kilometraje_en_mantenimiento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Kilometraje en Mantenimiento
                </label>
                <input type="number" id="kilometraje_en_mantenimiento" wire:model="kilometraje_en_mantenimiento" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="horometro_en_mantenimiento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horómetro en Mantenimiento
                </label>
                <input type="number" id="horometro_en_mantenimiento" wire:model="horometro_en_mantenimiento" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Motivo y Descripción -->
        <div>
            <label for="motivo_ingreso" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Motivo de Ingreso
            </label>
            <textarea id="motivo_ingreso" wire:model="motivo_ingreso" rows="2" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <div>
            <label for="descripcion_trabajo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Descripción del Trabajo <span class="text-red-500">*</span>
            </label>
            <textarea id="descripcion_trabajo" wire:model="descripcion_trabajo" rows="3" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error(descripcion_trabajo) border-red-500 dark:border-red-600 @enderror"></textarea>
            @error(descripcion_trabajo)
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="trabajos_realizados" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Trabajos Realizados
            </label>
            <textarea id="trabajos_realizados" wire:model="trabajos_realizados" rows="3" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <!-- Costos -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label for="costo_repuestos" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Costo Repuestos
                </label>
                <input type="number" id="costo_repuestos" wire:model.live="costo_repuestos" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="costo_mano_obra" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Costo Mano de Obra
                </label>
                <input type="number" id="costo_mano_obra" wire:model.live="costo_mano_obra" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="costo_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Costo Total
                </label>
                <input type="number" id="costo_total" wire:model="costo_total" min="0" readonly
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white">
            </div>

            <div>
                <label for="horas_trabajadas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horas Trabajadas
                </label>
                <input type="number" id="horas_trabajadas" wire:model="horas_trabajadas" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Técnico y Conductor -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="taller_proveedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Taller/Proveedor
                </label>
                <input type="text" id="taller_proveedor" wire:model="taller_proveedor" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="tecnico_responsable_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Técnico Responsable
                </label>
                <select id="tecnico_responsable_id" wire:model="tecnico_responsable_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sin asignar</option>
                    @foreach($tecnicos as $tecnico)
                        <option value="{{$tecnico->id}}">{{$tecnico->name}} ({{$tecnico->email}})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="conductor_asignado_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Conductor Asignado
                </label>
                <select id="conductor_asignado_id" wire:model="conductor_asignado_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sin asignar</option>
                    @foreach($conductores as $conductor)
                        <option value="{{$conductor->id}}">{{$conductor->nombre_completo}} ({{$conductor->rut}})</option>
                    @endforeach
                </select>
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
            <a href="{{route(mantenimientos.index)}}" 
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                Cancelar
            </a>
            <button type="submit" 
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
                {{ $mantenimientoId ? "Actualizar" : "Crear" }} Mantenimiento
            </button>
        </div>
    </form>
</div>
