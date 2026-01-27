# Estándar de DataTables para la Aplicación Melichinkul

**Versión:** 1.0  
**Última actualización:** 2026-01-27

Este documento describe el estándar oficial para implementar DataTables en todas las tablas de la aplicación Melichinkul. **Todas las tablas deben seguir este estándar para mantener consistencia en toda la aplicación.**

## Características Estándar

Todas las tablas que usen `initDataTable()` incluyen automáticamente:

1. **Botones de Exportación (Exporta TODAS las filas del servidor):**
   - Excel (.xlsx) - Con formato y estilos
   - CSV - Para análisis externo
   - Imprimir - Vista de impresión

2. **Selector de Columnas:**
   - Botón "Columnas" que permite mostrar/ocultar columnas
   - Las columnas con clase `no-toggle` no aparecen en el selector

3. **Modo Oscuro:**
   - Soporte automático para modo oscuro
   - Estilos adaptados para mejor contraste

4. **Configuración por Defecto:**
   - Idioma: Español
   - Paginación: 25 registros por página
   - Opciones: 10, 25, 50, 100, Todos
   - Responsive: Activado
   - Server-side processing: Activado (para grandes volúmenes)
   - Búsqueda en tiempo real
   - Ordenamiento por columnas

## Estructura de Archivos

```
app/
  Http/Controllers/
    [Entity]Controller.php          # Controlador con método index() y export()
  Exports/
    [Entity]Export.php              # Clase de exportación usando Laravel Excel

resources/
  views/
    [entity]/
      index.blade.php               # Vista con la tabla
  js/
    datatables-config.js            # Configuración estándar (reutilizable)

public/js/
    datatables-config.js            # Copia compilada del archivo JS
```

## Implementación Paso a Paso

### 1. Controlador

```php
<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Exports\VehiclesExport;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $vehicles = Vehicle::with(['category', 'currentDriver'])->select('vehicles.*');

            // Aplicar filtros si vienen en la petición
            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $vehicles->where(function ($query) use ($search) {
                    $query->where('license_plate', 'like', "%{$search}%")
                          ->orWhere('brand', 'like', "%{$search}%")
                          ->orWhere('model', 'like', "%{$search}%");
                });
            }

            return DataTables::of($vehicles)
                ->addColumn('category_name', function ($vehicle) {
                    return $vehicle->category->name ?? 'Sin categoría';
                })
                ->addColumn('status_badge', function ($vehicle) {
                    // Badge HTML con estilos
                    return "<span class='...'>...</span>";
                })
                ->addColumn('actions', function ($vehicle) {
                    return "
                        <div class='flex justify-end space-x-3'>
                            <a href='...' title='Ver detalles'>
                                <i class='fas fa-eye'></i>
                            </a>
                            <a href='...' title='Editar'>
                                <i class='fas fa-edit'></i>
                            </a>
                            <button onclick='deleteEntity({$vehicle->id})' title='Eliminar'>
                                <i class='fas fa-trash-alt'></i>
                            </button>
                        </div>
                    ";
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('vehicles.index');
    }

    public function export(Request $request, $format)
    {
        $filters = [
            'category_id' => $request->get('category_id'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $filename = 'vehicles_' . date('Y-m-d_His');

        switch ($format) {
            case 'excel':
                return Excel::download(new VehiclesExport($filters), $filename . '.xlsx');
            case 'csv':
                return Excel::download(new VehiclesExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
            default:
                return redirect()->back()->with('error', 'Formato no válido');
        }
    }
}
```

### 2. Clase de Exportación

```php
<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VehiclesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Vehicle::with(['category', 'currentDriver']);

        // Aplicar los mismos filtros que la tabla
        if (isset($this->filters['category_id']) && $this->filters['category_id']) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (isset($this->filters['status']) && $this->filters['status']) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['search']) && $this->filters['search']) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('license_plate', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('license_plate');
    }

    public function headings(): array
    {
        return [
            'Patente',
            'Marca',
            'Modelo',
            // ... más columnas
        ];
    }

    public function map($vehicle): array
    {
        return [
            $vehicle->license_plate,
            $vehicle->brand,
            $vehicle->model,
            // ... más datos
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            // ... más anchos
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
```

### 3. Vista Blade

```blade
@extends('layouts.app')

@section('title', 'Vehículos')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Vehículos</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de la flota</p>
        </div>
        <a href="{{ route('vehicles.create') }}" 
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
            Nuevo Vehículo
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="vehicles-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Patente</th>
                            <th>Marca / Modelo</th>
                            <th>Año</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Conductor</th>
                            <th>Kilometraje</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables llenará esto automáticamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/datatables-config.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = initDataTable('vehicles-table', {
        ajax: {
            url: "{{ route('vehicles.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'license_plate', name: 'license_plate' },
            { 
                data: 'brand', 
                name: 'brand',
                render: function(data, type, row) {
                    return '<div class="font-medium">' + data + '</div>' +
                           '<div class="text-sm text-gray-500 dark:text-gray-400">' + row.model + '</div>';
                }
            },
            { data: 'year', name: 'year' },
            { data: 'category_name', name: 'category.name' },
            { data: 'status_badge', name: 'status', orderable: true, searchable: false },
            { data: 'driver_name', name: 'currentDriver.full_name' },
            { 
                data: 'current_mileage', 
                name: 'current_mileage',
                render: function(data) {
                    return data ? new Intl.NumberFormat('es-CL').format(data) + ' km' : '-';
                }
            },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'no-export' }
        ],
        order: [[0, 'asc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4, 5, 6] },
            { className: "text-right", targets: [7] }
        ]
    });
});

// Función para eliminar (disponible globalmente)
window.deleteVehicle = function(id) {
    if (confirm('¿Estás seguro de eliminar este vehículo?')) {
        fetch('/vehicles/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#vehicles-table').DataTable().ajax.reload();
                alert(data.message);
            } else {
                alert('Error al eliminar.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar.');
        });
    }
}
</script>
@endpush
@endsection
```

### 4. Rutas

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    // Listado (soporta AJAX para DataTables)
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    
    // Exportación (POST)
    Route::post('/vehicles/export/{format}', [VehicleController::class, 'export'])
        ->name('vehicles.export');
    
    // CRUD estándar
    Route::get('/vehicles/create', ...);
    Route::get('/vehicles/{id}/edit', ...);
    Route::get('/vehicles/{id}', ...);
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
});
```

## Convenciones de Nombres

### Tablas HTML
- ID de tabla: `{entity}-table` (ej: `vehicles-table`, `maintenances-table`)
- Clase: `display nowrap w-full`

### Rutas de Exportación
- Patrón: `/{entity}/export/{format}`
- Formatos: `excel`, `csv`
- Método: `POST`

### Funciones JavaScript
- Función de eliminación: `window.delete{Entity} = function(id) { ... }`
- Ejemplo: `deleteVehicle`, `deleteMaintenance`, `deleteDriver`

## Excluir Columnas

### De Exportación
Agrega `className: 'no-export'` a la columna:

```javascript
{ data: 'actions', name: 'actions', className: 'no-export' }
```

### Del Selector de Columnas
Agrega `className: 'no-toggle'` a la columna:

```javascript
{ data: 'id', name: 'id', className: 'no-toggle' }
```

## Iconos de Acción Estándar

Todos los botones de acción deben usar Font Awesome:

- **Ver**: `<i class="fas fa-eye"></i>` - Color: `text-indigo-600 dark:text-indigo-400`
- **Editar**: `<i class="fas fa-edit"></i>` - Color: `text-blue-600 dark:text-blue-400`
- **Eliminar**: `<i class="fas fa-trash-alt"></i>` - Color: `text-red-600 dark:text-red-400`

## Personalización

### Agregar Botones Personalizados

```javascript
const table = initDataTable('my-table', {
    // ... otras opciones
    buttons: [
        {
            text: '<i class="fas fa-plus"></i> Nuevo',
            className: 'btn-custom',
            action: function(e, dt, node, config) {
                window.location.href = '/my-entity/create';
            }
        }
    ]
});
```

### Cambiar Configuración por Defecto

```javascript
const table = initDataTable('my-table', {
    pageLength: 50,  // Cambiar paginación
    order: [[1, 'desc']],  // Cambiar orden inicial
    // ... otras opciones personalizadas
});
```

## Ejemplo Completo

Ver `resources/views/vehiculos/index.blade.php` y `app/Http/Controllers/VehicleController.php` para un ejemplo completo de implementación.

## Checklist de Implementación

Al crear una nueva tabla con DataTables, asegúrate de:

- [ ] Crear el controlador con método `index()` que retorna DataTables JSON
- [ ] Crear el controlador con método `export()` para exportaciones
- [ ] Crear la clase Export (ej: `VehiclesExport`) en `app/Exports/`
- [ ] Crear la vista Blade con la tabla HTML
- [ ] Usar `initDataTable()` con la configuración estándar
- [ ] Agregar rutas GET (index) y POST (export)
- [ ] Agregar función JavaScript para eliminar (ej: `deleteVehicle`)
- [ ] Usar iconos Font Awesome en botones de acción
- [ ] Marcar columna de acciones con `className: 'no-export'`
- [ ] Probar exportación Excel y CSV
- [ ] Probar selector de columnas
- [ ] Verificar modo oscuro

## Notas Importantes

1. **Exportación Exporta TODAS las filas**: Los botones de exportación hacen una petición POST al servidor que consulta TODOS los registros (no solo los 10-25 visibles en pantalla), respetando los filtros aplicados. Si hay 1000 filas y aplicas un filtro, se exportarán todas las que cumplan el filtro.

2. **Server-Side Processing**: Todas las tablas usan procesamiento del lado del servidor para mejor rendimiento con grandes volúmenes de datos.

3. **Modo Oscuro**: Los estilos se aplican automáticamente según el tema activo. El contraste está optimizado para legibilidad.

4. **Responsive**: Las tablas se adaptan automáticamente a dispositivos móviles usando DataTables Responsive.

5. **Idioma**: Todas las tablas están en español por defecto (traducción automática de DataTables).

6. **Iconos**: Todos los botones de acción usan Font Awesome para mantener consistencia visual.

## Mapeo de Rutas de Exportación

La función `exportToServer()` mapea automáticamente los IDs de tabla a rutas de exportación:

- `vehicles-table` → `/vehiculos/export/{format}`
- `maintenances-table` → `/mantenimientos/export/{format}`
- `drivers-table` → `/conductores/export/{format}`
- `certifications-table` → `/certificaciones/export/{format}`

Si necesitas agregar un nuevo mapeo, edita `resources/js/datatables-config.js` en la función `exportToServer()`.
