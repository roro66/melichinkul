<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <form wire:submit="save" class="space-y-6">
        @if(!$maintenance && $templates->isNotEmpty())
        <div class="p-4 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800">
            <label for="template_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usar plantilla</label>
            <select id="template_id" wire:model.live="template_id"
                class="w-full max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                <option value="">Ninguna</option>
                @foreach($templates as $t)
                <option value="{{ $t->id }}">{{ $t->name }}@if($t->type) ({{ __('mantenimiento.types.' . $t->type, [], 'es') }})@endif</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Al guardar, se copiarán el tipo, descripción y repuestos de la plantilla al mantenimiento.</p>
        </div>
        @endif
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
                    <option value="pending_approval">Pendiente de aprobación</option>
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
                <label for="parts_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Total Repuestos
                </label>
                <input type="number" id="parts_cost" wire:model.live="parts_cost" min="0" readonly
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="labor_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Mano de obra
                </label>
                <input type="number" id="labor_cost" wire:model.live="labor_cost" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="total_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Costo Total
                </label>
                <input type="number" id="total_cost" wire:model.live="total_cost" min="0" readonly
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Se calcula automáticamente con repuestos + mano de obra.</p>
            </div>

            <div>
                <label for="horas_trabajadas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horas Trabajadas
                </label>
                <input type="number" id="hours_worked" wire:model="hours_worked" step="0.01" min="0" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
        <div x-data="{ purchaseModalOpen: false }">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900/40">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Detalle de repuestos y documentos</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Gestiona items de bodega y manuales en un modal tipo factura.</p>
                    </div>
                    <button type="button" @click="purchaseModalOpen = true"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg text-sm">
                        Ver / Editar formulario
                    </button>
                </div>
            </div>

            <!-- Modal: Repuestos dinámicos (fuera del flujo Alpine del form completo; z-index por encima del layout) -->
            <div
                x-show="purchaseModalOpen"
                x-cloak
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                x-transition.opacity
            >
                <div class="absolute inset-0 bg-white/70 dark:bg-black/80 backdrop-blur-[2px]" @click="purchaseModalOpen = false"></div>
                <div class="relative z-10 w-[96vw] max-w-[96vw] max-h-[94vh] overflow-hidden rounded-xl bg-white dark:bg-gray-900 border-2 border-indigo-200 dark:border-indigo-700 shadow-2xl ring-1 ring-black/5 dark:ring-white/10">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-indigo-100 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Repuestos y documentos de compra</h3>
                        <button type="button" @click="purchaseModalOpen = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-6 overflow-auto max-h-[calc(94vh-144px)] space-y-4">
                        <div class="flex justify-end">
                            <button type="button" wire:click="addPurchaseItemRow"
                                class="inline-flex items-center px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm">
                                <i class="fas fa-plus mr-2"></i>Agregar línea
                            </button>
                        </div>

                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Repuesto bodega (opcional)</th>
                                        <th class="px-3 py-2 text-left">Producto</th>
                                        <th class="px-3 py-2 text-left">Proveedor</th>
                                        <th class="px-3 py-2 text-left">N° documento</th>
                                        <th class="px-3 py-2 text-right">Precio</th>
                                        <th class="px-3 py-2 text-right">Cantidad</th>
                                        <th class="px-3 py-2 text-right">Subtotal</th>
                                        <th class="px-3 py-2 text-left">Imagen</th>
                                        <th class="px-3 py-2 w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($purchaseItems as $index => $line)
                                        <tr>
                                            <td class="px-3 py-2 align-top min-w-[240px]">
                                                <select wire:model.live="purchaseItems.{{ $index }}.spare_part_id"
                                                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700">
                                                    <option value="">Manual (sin bodega)</option>
                                                    @foreach($spareParts as $sp)
                                                        <option value="{{ $sp->id }}">{{ $sp->code }} - {{ $sp->description }}</option>
                                                    @endforeach
                                                </select>
                                                @can('spare_parts.create')
                                                    <button type="button"
                                                        class="mt-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer"
                                                        wire:click.stop="openQuickSparePartModal({{ $index }})">
                                                        + Nuevo repuesto
                                                    </button>
                                                @endcan
                                            </td>
                                            <td class="px-3 py-2 align-top min-w-[220px]">
                                                <input type="text" wire:model.live="purchaseItems.{{ $index }}.product_name"
                                                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                                                    placeholder="Nombre producto">
                                                @error("purchaseItems.$index.product_name")
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td class="px-3 py-2 align-top min-w-[180px]">
                                                <input type="text" wire:model.live="purchaseItems.{{ $index }}.supplier_name"
                                                    list="supplier-datalist-{{ $index }}"
                                                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                                                    placeholder="Proveedor">
                                                <datalist id="supplier-datalist-{{ $index }}">
                                                    @foreach($suppliers as $sup)
                                                        <option value="{{ $sup->name }}"></option>
                                                    @endforeach
                                                </datalist>
                                                @can('suppliers.create')
                                                    <button type="button"
                                                        class="mt-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer block"
                                                        wire:click.stop="openQuickSupplierModal({{ $index }})">
                                                        + Nuevo proveedor
                                                    </button>
                                                @endcan
                                                @error("purchaseItems.$index.supplier_name")
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td class="px-3 py-2 align-top min-w-[160px]">
                                                <input type="text" wire:model.live="purchaseItems.{{ $index }}.document_number"
                                                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700"
                                                    placeholder="Factura/Boleta/Guía">
                                                @error("purchaseItems.$index.document_number")
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td class="px-3 py-2 align-top min-w-[130px]">
                                                <input type="number" min="0" wire:model.live="purchaseItems.{{ $index }}.unit_price"
                                                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-right">
                                                @error("purchaseItems.$index.unit_price")
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td class="px-3 py-2 align-top min-w-[110px]">
                                                <input type="number" min="1" wire:model.live="purchaseItems.{{ $index }}.quantity"
                                                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-right">
                                                @error("purchaseItems.$index.quantity")
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td class="px-3 py-2 align-top min-w-[130px] text-right font-semibold text-gray-900 dark:text-white">
                                                ${{ number_format((int) ($line['line_total'] ?? 0), 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 align-top min-w-[220px]">
                                                @if(!empty($line['document_image_path']))
                                                    <a href="{{ Storage::url($line['document_image_path']) }}" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline block mb-1">
                                                        Ver archivo actual
                                                    </a>
                                                @endif
                                                <input type="file" wire:model="purchaseItems.{{ $index }}.document_image" accept=".pdf,.jpg,.jpeg,.png"
                                                    class="w-full text-xs">
                                                @error("purchaseItems.$index.document_image")
                                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </td>
                                            <td class="px-3 py-2 align-top text-right">
                                                <button type="button" wire:click="removePurchaseItemRow({{ $index }})"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <td colspan="6" class="px-3 py-2 text-right font-semibold">Total repuestos</td>
                                        <td class="px-3 py-2 text-right font-bold text-gray-900 dark:text-white">
                                            ${{ number_format((int) $parts_cost, 0, ',', '.') }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-indigo-100 dark:border-indigo-800 bg-gray-50 dark:bg-gray-800/70">
                        <button type="button" @click="purchaseModalOpen = false"
                            class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-sm">
                            Cerrar
                        </button>
                        <button type="button" @click="purchaseModalOpen = false"
                            class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
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
                    Técnico responsable (mecánico) <span class="text-red-500">*</span>
                </label>
                <select id="responsible_technician_id" wire:model="responsible_technician_id" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('responsible_technician_id') border-red-500 @enderror">
                    <option value="">Seleccionar técnico</option>
                    @foreach($technicians as $technician)
                        <option value="{{$technician->id}}">{{$technician->name}} ({{$technician->email}})</option>
                    @endforeach
                </select>
                @error('responsible_technician_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
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

        @if($quickSupplierModalOpen)
            <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 dark:bg-black/75" wire:click="closeQuickSupplierModal" aria-hidden="true"></div>
                <div class="relative w-full max-w-md rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 shadow-2xl p-6" @click.stop>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Nuevo proveedor</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Se guarda en el catálogo y se asigna a esta línea.</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="quickSupplierName" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('quickSupplierName')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">RUT</label>
                            <input type="text" wire:model="quickSupplierRut" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('quickSupplierRut')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Contacto</label>
                            <input type="text" wire:model="quickSupplierContactName" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('quickSupplierContactName')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Teléfono</label>
                                <input type="text" wire:model="quickSupplierPhone" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @error('quickSupplierPhone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Correo</label>
                                <input type="email" wire:model="quickSupplierEmail" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @error('quickSupplierEmail')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" wire:click="closeQuickSupplierModal" class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancelar
                        </button>
                        <button type="button" wire:click="saveQuickSupplier" wire:loading.attr="disabled" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-60">
                            <span wire:loading.remove wire:target="saveQuickSupplier">Guardar</span>
                            <span wire:loading wire:target="saveQuickSupplier">Guardando…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($quickSparePartModalOpen)
            <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 dark:bg-black/75" wire:click="closeQuickSparePartModal" aria-hidden="true"></div>
                <div class="relative w-full max-w-lg rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 shadow-2xl p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Nuevo repuesto en bodega</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Se crea activo y queda seleccionado en esta línea.</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Código <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="quickSpareCode" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('quickSpareCode')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Descripción <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="quickSpareDescription" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('quickSpareDescription')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Marca</label>
                            <input type="text" wire:model="quickSpareBrand" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('quickSpareBrand')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Categoría <span class="text-red-500">*</span></label>
                            <select wire:model="quickSpareCategory" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach($sparePartCategories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}">{{ $catLabel }}</option>
                                @endforeach
                            </select>
                            @error('quickSpareCategory')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Precio referencia ($)</label>
                            <input type="number" min="0" wire:model="quickSpareReferencePrice" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('quickSpareReferencePrice')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" wire:click="closeQuickSparePartModal" class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancelar
                        </button>
                        <button type="button" wire:click="saveQuickSparePart" wire:loading.attr="disabled" class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-60">
                            <span wire:loading.remove wire:target="saveQuickSparePart">Guardar</span>
                            <span wire:loading wire:target="saveQuickSparePart">Guardando…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </form>
</div>
