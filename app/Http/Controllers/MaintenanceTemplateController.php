<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceTemplate;
use App\Models\SparePart;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MaintenanceTemplateController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $templates = MaintenanceTemplate::withCount('spareParts')->select('maintenance_templates.*');

            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $templates->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            }

            return DataTables::of($templates)
                ->addColumn('type_badge', function (MaintenanceTemplate $template) {
                    if (! $template->type) {
                        return '<span class="text-gray-400 dark:text-gray-500">—</span>';
                    }
                    $colors = [
                        'preventive' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                        'corrective' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300',
                        'inspection' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
                    ];
                    $color = $colors[$template->type] ?? $colors['preventive'];
                    $text = __('mantenimiento.types.' . $template->type, [], 'es');
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$text}</span>";
                })
                ->addColumn('description_short', function (MaintenanceTemplate $template) {
                    if (! $template->description) {
                        return '—';
                    }
                    return strlen($template->description) > 60
                        ? substr($template->description, 0, 60) . '...'
                        : $template->description;
                })
                ->addColumn('actions', function (MaintenanceTemplate $template) {
                    if (! auth()->user()->can('maintenances.create')) {
                        return '';
                    }
                    return "
                        <div class='flex justify-end space-x-3'>
                            <a href='" . route('plantillas.edit', $template->id) . "' 
                               class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300'
                               title='Editar'><i class='fas fa-edit'></i></a>
                            <button onclick='deletePlantilla(" . $template->id . ")' 
                                    class='text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 cursor-pointer bg-transparent border-none'
                                    title='Eliminar'><i class='fas fa-trash-alt'></i></button>
                        </div>
                    ";
                })
                ->rawColumns(['type_badge', 'actions'])
                ->make(true);
        }

        return view('plantillas.index');
    }

    public function create()
    {
        $spareParts = SparePart::where('active', true)->orderBy('code')->get();
        return view('plantillas.create', compact('spareParts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:preventive,corrective,inspection',
        ]);

        $template = MaintenanceTemplate::create($validated);

        $this->syncSpareParts($template, $request->input('spare_parts', []));

        return redirect()->route('plantillas.index')->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(int $id)
    {
        $template = MaintenanceTemplate::with('spareParts')->findOrFail($id);
        $spareParts = SparePart::where('active', true)->orderBy('code')->get();
        return view('plantillas.edit', compact('template', 'spareParts'));
    }

    public function update(Request $request, int $id)
    {
        $template = MaintenanceTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|in:preventive,corrective,inspection',
        ]);

        $template->update($validated);
        $this->syncSpareParts($template, $request->input('spare_parts', []));

        return redirect()->route('plantillas.index')->with('success', 'Plantilla actualizada correctamente.');
    }

    public function destroy(int $id)
    {
        $template = MaintenanceTemplate::findOrFail($id);
        $template->delete();
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Plantilla eliminada.']);
        }
        return redirect()->route('plantillas.index')->with('success', 'Plantilla eliminada.');
    }

    /**
     * @param array<int, array{spare_part_id?: int, quantity?: int}> $items
     */
    private function syncSpareParts(MaintenanceTemplate $template, array $items): void
    {
        $sync = [];
        foreach ($items as $row) {
            $sparePartId = isset($row['spare_part_id']) ? (int) $row['spare_part_id'] : 0;
            $quantity = isset($row['quantity']) ? max(1, (int) $row['quantity']) : 1;
            if ($sparePartId > 0) {
                $sync[$sparePartId] = ['quantity' => $quantity];
            }
        }
        $template->spareParts()->sync($sync);
    }
}
