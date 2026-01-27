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

            // Filtro por categoría
            if (request()->has('columns') && request()->get('columns')[3]['search']['value']) {
                $categoryId = request()->get('columns')[3]['search']['value'];
                if ($categoryId) {
                    $vehicles->where('category_id', $categoryId);
                }
            }

            // Filtro por estado
            if (request()->has('columns') && request()->get('columns')[4]['search']['value']) {
                $status = request()->get('columns')[4]['search']['value'];
                if ($status) {
                    $vehicles->where('status', $status);
                }
            }

            return DataTables::of($vehicles)
                ->addColumn('category_name', function ($vehicle) {
                    return $vehicle->category->name ?? 'Sin categoría';
                })
                ->addColumn('driver_name', function ($vehicle) {
                    return $vehicle->currentDriver->full_name ?? 'Sin asignar';
                })
                ->addColumn('status_badge', function ($vehicle) {
                    $statusColors = [
                        'active' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                        'inactive' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                        'maintenance' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                        'decommissioned' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                    ];
                    $color = $statusColors[$vehicle->status] ?? $statusColors['inactive'];
                    $statusText = ucfirst($vehicle->status);
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$statusText}</span>";
                })
                ->addColumn('actions', function ($vehicle) {
                    return "
                        <div class='flex justify-end space-x-3'>
                            <a href='" . route('vehiculos.show', $vehicle->id) . "' 
                               class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors duration-150'
                               title='Ver detalles'>
                                <i class='fas fa-eye'></i>
                            </a>
                            <a href='" . route('vehiculos.edit', $vehicle->id) . "' 
                               class='text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-150'
                               title='Editar'>
                                <i class='fas fa-edit'></i>
                            </a>
                            <button onclick='deleteVehicle(" . $vehicle->id . ")' 
                                    class='text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-150 cursor-pointer bg-transparent border-none'
                                    title='Eliminar'>
                                <i class='fas fa-trash-alt'></i>
                            </button>
                        </div>
                    ";
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('vehiculos.index');
    }

    public function destroy($id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->delete();
            
            return response()->json([
                'success' => true, 
                'message' => 'Vehículo eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al eliminar el vehículo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request, $format)
    {
        // Obtener filtros de la petición
        $filters = [
            'category_id' => $request->get('category_id'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        // Limpiar valores vacíos
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $filename = 'vehiculos_' . date('Y-m-d_His');

        switch ($format) {
            case 'excel':
                return Excel::download(new VehiclesExport($filters), $filename . '.xlsx');
            case 'csv':
                return Excel::download(new VehiclesExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
            default:
                return redirect()->back()->with('error', 'Formato de exportación no válido');
        }
    }
}
