<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DriverController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $drivers = Driver::select('drivers.*');

            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $drivers->where(function ($query) use ($search) {
                    $query->where('rut', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $activeFilter = request()->input('columns.6.search.value');
            if ($activeFilter !== null && $activeFilter !== '') {
                $active = in_array(strtolower($activeFilter), ['1', 'true', 'activo', 'yes'], true);
                $drivers->where('active', $active);
            }

            return DataTables::of($drivers)
                ->addColumn('license_status_badge', function (Driver $driver) {
                    if (! $driver->license_expiration_date) {
                        return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'>Sin fecha</span>";
                    }
                    if ($driver->hasExpiredLicense()) {
                        return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300'>Vencida</span>";
                    }
                    if ($driver->licenseExpiringSoon(30)) {
                        return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300'>Por vencer</span>";
                    }
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'>Vigente</span>";
                })
                ->addColumn('license_expiration_formatted', function (Driver $driver) {
                    return $driver->license_expiration_date ? $driver->license_expiration_date->format('d/m/Y') : '—';
                })
                ->addColumn('active_badge', function (Driver $driver) {
                    $color = $driver->active ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                    $label = $driver->active ? 'Activo' : 'Inactivo';
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$label}</span>";
                })
                ->addColumn('actions', function (Driver $driver) {
                    return "
                        <div class='flex justify-end space-x-3'>
                            <a href='" . route('conductores.show', $driver->id) . "' 
                               class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors duration-150'
                               title='Ver detalles'>
                                <i class='fas fa-eye'></i>
                            </a>
                            <a href='" . route('conductores.edit', $driver->id) . "' 
                               class='text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors duration-150'
                               title='Editar'>
                                <i class='fas fa-edit'></i>
                            </a>
                            <button onclick='deleteDriver(" . $driver->id . ")' 
                                    class='text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors duration-150 cursor-pointer bg-transparent border-none'
                                    title='Eliminar'>
                                <i class='fas fa-trash-alt'></i>
                            </button>
                        </div>
                    ";
                })
                ->rawColumns(['license_status_badge', 'active_badge', 'actions'])
                ->make(true);
        }

        return view('conductores.index');
    }

    public function create()
    {
        return view('conductores.create');
    }

    public function store(Request $request)
    {
        // Handled by Livewire DriverForm
        return redirect()->route('conductores.index');
    }

    public function show(int $id)
    {
        $driver = Driver::with(['assignedVehicles', 'maintenances'])->findOrFail($id);
        return view('conductores.show', compact('driver'));
    }

    public function edit(int $id)
    {
        return view('conductores.edit', ['id' => $id]);
    }

    public function update(Request $request, int $id)
    {
        // Handled by Livewire DriverForm
        return redirect()->route('conductores.index');
    }

    public function destroy(int $id)
    {
        try {
            $driver = Driver::findOrFail($id);
            $driver->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conductor eliminado correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el conductor: ' . $e->getMessage(),
            ], 500);
        }
    }
}
