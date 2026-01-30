<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SparePartController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $items = SparePart::select('spare_parts.*');

            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $items->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            }

            $categoryFilter = request()->input('columns.3.search.value');
            if ($categoryFilter !== null && $categoryFilter !== '') {
                $items->where('category', $categoryFilter);
            }

            $activeFilter = request()->input('columns.5.search.value');
            if ($activeFilter !== null && $activeFilter !== '') {
                $active = in_array(strtolower($activeFilter), ['1', 'true', 'activo', 'yes'], true);
                $items->where('active', $active);
            }

            return DataTables::of($items)
                ->addColumn('category_label', function (SparePart $item) {
                    return SparePart::CATEGORIES[$item->category] ?? $item->category;
                })
                ->addColumn('reference_price_formatted', function (SparePart $item) {
                    return $item->reference_price !== null ? '$' . number_format($item->reference_price, 0, ',', '.') : '—';
                })
                ->addColumn('active_badge', function (SparePart $item) {
                    $color = $item->active ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
                    $label = $item->active ? 'Activo' : 'Inactivo';
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$label}</span>";
                })
                ->addColumn('actions', function (SparePart $item) {
                    return "
                        <div class='flex justify-end space-x-3'>
                            <a href='" . route('repuestos.edit', $item->id) . "' 
                               class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300'
                               title='Editar'><i class='fas fa-edit'></i></a>
                            <button onclick='deleteSparePart(" . $item->id . ")' 
                                    class='text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 cursor-pointer bg-transparent border-none'
                                    title='Eliminar'><i class='fas fa-trash-alt'></i></button>
                        </div>
                    ";
                })
                ->rawColumns(['active_badge', 'actions'])
                ->make(true);
        }

        return view('repuestos.index');
    }

    public function create()
    {
        return view('repuestos.create', ['categories' => SparePart::CATEGORIES]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:64|unique:spare_parts,code',
            'description' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(SparePart::CATEGORIES)),
            'reference_price' => 'nullable|integer|min:0',
            'has_expiration' => 'boolean',
            'active' => 'boolean',
        ]);
        $validated['has_expiration'] = $request->boolean('has_expiration');
        $validated['active'] = $request->boolean('active', true);

        SparePart::create($validated);
        return redirect()->route('repuestos.index')->with('success', 'Repuesto creado correctamente.');
    }

    public function edit(int $id)
    {
        $sparePart = SparePart::findOrFail($id);
        return view('repuestos.edit', [
            'sparePart' => $sparePart,
            'categories' => SparePart::CATEGORIES,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $sparePart = SparePart::findOrFail($id);
        $validated = $request->validate([
            'code' => 'required|string|max:64|unique:spare_parts,code,' . $id,
            'description' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category' => 'required|string|in:' . implode(',', array_keys(SparePart::CATEGORIES)),
            'reference_price' => 'nullable|integer|min:0',
            'has_expiration' => 'boolean',
            'active' => 'boolean',
        ]);
        $validated['has_expiration'] = $request->boolean('has_expiration');
        $validated['active'] = $request->boolean('active');

        $sparePart->update($validated);
        return redirect()->route('repuestos.index')->with('success', 'Repuesto actualizado correctamente.');
    }

    public function destroy(int $id)
    {
        $sparePart = SparePart::findOrFail($id);
        $sparePart->delete();
        return response()->json(['success' => true, 'message' => 'Repuesto eliminado correctamente.']);
    }
}
