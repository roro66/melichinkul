<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index()
    {
        if (request()->ajax() || request()->has('draw')) {
            $suppliers = Supplier::select('suppliers.*');

            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $suppliers->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('rut', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $activeFilter = request()->input('columns.5.search.value');
            if ($activeFilter !== null && $activeFilter !== '') {
                $active = in_array(strtolower($activeFilter), ['1', 'true', 'activo', 'yes'], true);
                $suppliers->where('active', $active);
            }

            return DataTables::of($suppliers)
                ->addColumn('active_badge', function (Supplier $s) {
                    $color = $s->active ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                    $label = $s->active ? 'Activo' : 'Inactivo';
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$label}</span>";
                })
                ->addColumn('actions', function (Supplier $s) {
                    return "
                        <div class='flex justify-end space-x-3'>
                            <a href='" . route('proveedores.edit', $s->id) . "' 
                               class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300'
                               title='Editar'><i class='fas fa-edit'></i></a>
                            <button onclick='deleteSupplier(" . $s->id . ")' 
                                    class='text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 cursor-pointer bg-transparent border-none'
                                    title='Eliminar'><i class='fas fa-trash-alt'></i></button>
                        </div>
                    ";
                })
                ->rawColumns(['active_badge', 'actions'])
                ->make(true);
        }

        return view('proveedores.index');
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'nullable|string|max:32',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:64',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $validated['active'] = $request->boolean('active', true);

        Supplier::create($validated);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(int $id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('proveedores.edit', ['supplier' => $supplier]);
    }

    public function update(Request $request, int $id)
    {
        $supplier = Supplier::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'nullable|string|max:32',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:64',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $validated['active'] = $request->boolean('active');

        $supplier->update($validated);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json(['success' => true, 'message' => 'Proveedor eliminado correctamente.']);
    }
}
