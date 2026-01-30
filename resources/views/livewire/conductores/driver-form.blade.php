<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="rut" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    RUT <span class="text-red-500">*</span>
                </label>
                <input type="text" id="rut" wire:model.blur="rut"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('rut') border-red-500 dark:border-red-600 @enderror"
                    placeholder="12345678-9">
                @error('rut')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Nombre completo <span class="text-red-500">*</span>
                </label>
                <input type="text" id="full_name" wire:model="full_name"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('full_name') border-red-500 dark:border-red-600 @enderror">
                @error('full_name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                <input type="text" id="phone" wire:model="phone"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" id="email" wire:model="email"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 dark:border-red-600 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="license_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nº licencia</label>
                <input type="text" id="license_number" wire:model="license_number"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="license_class" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Clase licencia</label>
                <input type="text" id="license_class" wire:model="license_class"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                    placeholder="B, C, etc.">
            </div>

            <div>
                <label for="license_issue_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha emisión licencia</label>
                <input type="date" id="license_issue_date" wire:model="license_issue_date"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="license_expiration_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha vencimiento licencia</label>
                <input type="date" id="license_expiration_date" wire:model="license_expiration_date"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('license_expiration_date') border-red-500 dark:border-red-600 @enderror">
                @error('license_expiration_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="observations" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                <textarea id="observations" wire:model="observations" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="active" wire:model="active"
                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                <label for="active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('conductores.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
                {{ $driverId ? 'Actualizar' : 'Guardar' }}
            </button>
        </div>
    </form>
</div>
