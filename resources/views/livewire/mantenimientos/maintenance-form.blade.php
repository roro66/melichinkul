<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form wire:submit="save" class="space-y-6">
        <!-- Información Básica -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="vehiculo_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Vehículo <span class="text-red-500">*</span>
                </label>
                <select id="vehicle_id" wire:model="vehicle_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error("vehicle_id") border-red-500 dark:border-red-600 @enderror">
                    <option value="">Seleccionar vehículo</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{$vehicle->id}}">{{$vehicle->license_plate}} - {{$vehicle->brand}} {{$vehicle->model}}</option>
                    @endforeach
                </select>
                @error("vehicle_id")
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tipo <span class="text-red-500">*</span>
                </label>
                <select id="type" wire:model="type" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="preventive">Preventivo</option>
                    <option value="corrective">Correctivo</option>
                    <option value="inspection">Inspección</option>
                </select>
            </div>

            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select id="status" wire:model="status" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="scheduled">Programado</option>
                    <option value="in_progress">En Proceso</option>
                    <option value="completed">Completado</option>
                    <option value="cancelled">Cancelado</option>
                </select>
            </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="fecha_programada" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha Programada <span class="text-red-500">*</span>
                </label>
                <input type="date" id="scheduled_date" wire:model="scheduled_date" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error("scheduled_date") border-red-500 dark:border-red-600 @enderror">
                @error("scheduled_date")
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de Inicio
                </label>
                <input type="date" id="start_date" wire:model="start_date" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de Fin
                </label>
                <input type="date" id="end_date" wire:model="end_date" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Kilometraje y Horómetro -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="kilometraje_en_mantenimiento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Kilometraje en Mantenimiento
                </label>
                <input type="number" id="mileage_at_maintenance" wire:model="mileage_at_maintenance" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="horometro_en_mantenimiento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horómetro en Mantenimiento
                </label>
                <input type="number" id="hours_at_maintenance" wire:model="hours_at_maintenance" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Motivo y Descripción -->
        <div>
            <label for="motivo_ingreso" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Motivo de Ingreso
            </label>
            <textarea id="entry_reason" wire:model="entry_reason" rows="2" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <div>
            <label for="descripcion_trabajo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Descripción del Trabajo <span class="text-red-500">*</span>
            </label>
            <textarea id="work_description" wire:model="work_description" rows="3" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error("work_description") border-red-500 dark:border-red-600 @enderror"></textarea>
            @error("work_description")
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="trabajos_realizados" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Trabajos Realizados
            </label>
            <textarea id="work_performed" wire:model="work_performed" rows="3" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <!-- Costos -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <label for="costo_repuestos" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Costo Repuestos
                </label>
                <input type="number" id="parts_cost" wire:model.live="parts_cost" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="costo_mano_obra" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Costo Mano de Obra
                </label>
                <input type="number" id="labor_cost" wire:model.live="labor_cost" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="costo_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Costo Total
                </label>
                <input type="number" id="total_cost" wire:model="total_cost" min="0" readonly
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white">
            </div>

            <div>
                <label for="horas_trabajadas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horas Trabajadas
                </label>
                <input type="number" id="hours_worked" wire:model="hours_worked" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <!-- Técnico y Conductor -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="taller_proveedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Taller/Proveedor
                </label>
                <input type="text" id="workshop_supplier" wire:model="workshop_supplier" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="tecnico_responsable_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Técnico Responsable
                </label>
                <select id="responsible_technician_id" wire:model="responsible_technician_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sin asignar</option>
                    @foreach($technicians as $technician)
                        <option value="{{$technician->id}}">{{$technician->name}} ({{$technician->email}})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="conductor_asignado_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Conductor Asignado
                </label>
                <select id="assigned_driver_id" wire:model="assigned_driver_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sin asignar</option>
                    @foreach($drivers as $driver)
                        <option value="{{$driver->id}}">{{$driver->full_name}} ({{$driver->rut}})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Observaciones -->
        <div>
            <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Observaciones
            </label>
            <textarea id="observations" wire:model="observations" rows="3" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <!-- Evidencia (opcional) -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Evidencia (opcional)</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="evidence_invoice" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Factura / Documento (PDF o imagen)
                    </label>
                    @if(isset($maintenance) && $maintenance->evidence_invoice_path)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            Actual: <a href="{{ Storage::url($maintenance->evidence_invoice_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver archivo</a>
                        </p>
                    @endif
                    <input type="file" id="evidence_invoice" wire:model="evidence_invoice" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50">
                    @error("evidence_invoice")
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máx. 10 MB. PDF, JPG o PNG.</p>
                </div>
                <div>
                    <label for="evidence_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Foto del trabajo realizado
                    </label>
                    @if(isset($maintenance) && $maintenance->evidence_photo_path)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            Actual: <a href="{{ Storage::url($maintenance->evidence_photo_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver archivo</a>
                        </p>
                    @endif
                    <input type="file" id="evidence_photo" wire:model="evidence_photo" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50">
                    @error("evidence_photo")
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máx. 10 MB. PDF, JPG o PNG.</p>
                </div>
            </div>
            <div wire:loading wire:target="evidence_invoice,evidence_photo" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Subiendo archivo…
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{route('mantenimientos.index')}}" 
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                Cancelar
            </a>
            <button type="submit" 
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
                {{ $maintenanceId ? "Actualizar" : "Crear" }} Mantenimiento
            </button>
        </div>
    </form>
</div>
