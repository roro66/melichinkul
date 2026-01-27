<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="patente" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Patente <span class="text-red-500">*</span>
                </label>
                <input type="text" id="license_plate" wire:model.blur="license_plate" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error("license_plate") border-red-500 dark:border-red-600 @enderror"
                    placeholder="ABCD12">
                @error("license_plate")
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Categoría
                </label>
                <select id="categoria_id" wire:model="categoria_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccionar categoría</option>
                    @foreach($categories as $category)
                        <option value="{{$category->id}}">{{$category->name}}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="marca" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Marca <span class="text-red-500">*</span>
                </label>
                <input type="text" id="brand" wire:model="brand" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error("brand") border-red-500 dark:border-red-600 @enderror">
                @error("brand")
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="modelo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Modelo <span class="text-red-500">*</span>
                </label>
                <input type="text" id="model" wire:model="model" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error("model") border-red-500 dark:border-red-600 @enderror">
                @error("model")
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="anio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Año <span class="text-red-500">*</span>
                </label>
                <input type="number" id="year" wire:model="year" min="1900" max="2100" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error("year") border-red-500 dark:border-red-600 @enderror">
                @error("year")
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tipo_combustible" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Tipo de Combustible <span class="text-red-500">*</span>
                </label>
                <select id="fuel_type" wire:model="fuel_type" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="gasoline">Gasolina</option>
                    <option value="diesel">Diesel</option>
                    <option value="gas">Gas</option>
                    <option value="electric">Eléctrico</option>
                    <option value="hybrid">Híbrido</option>
                </select>
            </div>

            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Estado <span class="text-red-500">*</span>
                </label>
                <select id="status" wire:model="status" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                    <option value="maintenance">En Mantenimiento</option>
                    <option value="decommissioned">Baja</option>
                </select>
            </div>

            <div>
                <label for="fecha_incorporacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fecha de Incorporación <span class="text-red-500">*</span>
                </label>
                <input type="date" id="incorporation_date" wire:model="incorporation_date" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error("incorporation_date") border-red-500 dark:border-red-600 @enderror">
                @error("incorporation_date")
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="conductor_actual_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Conductor Actual
                </label>
                <select id="current_driver_id" wire:model="current_driver_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Sin asignar</option>
                    @foreach($drivers as $driver)
                        <option value="{{$driver->id}}">{{$driver->full_name}} ({{ $driver->rut }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="numero_motor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Número de Motor
                </label>
                <input type="text" id="engine_number" wire:model="engine_number" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="numero_chasis" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Número de Chasis
                </label>
                <input type="text" id="chassis_number" wire:model="chassis_number" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="kilometraje_actual" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Kilometraje Actual
                </label>
                <input type="number" id="current_mileage" wire:model="current_mileage" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="horometro_actual" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horómetro Actual
                </label>
                <input type="number" id="current_hours" wire:model="current_hours" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="valor_compra" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Valor de Compra
                </label>
                <input type="number" id="purchase_value" wire:model="purchase_value" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div>
            <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Observaciones
            </label>
            <textarea id="observations" wire:model="observations" rows="3" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{route(vehiculos.index)}}" 
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                Cancelar
            </a>
            <button type="submit" 
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
                {{ $vehicleId ? "Actualizar" : "Crear" }} Vehículo
            </button>
        </div>
    </form>
</div>
