<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\MaintenanceChecklistCompletion;
use App\Models\MaintenanceChecklistItem;
use App\Models\MaintenanceSparePart;
use App\Models\SparePart;
use App\Exports\MaintenancesExport;
use App\Services\AuditService;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    public function __construct(
        protected AuditService $audit
    ) {}

    public function calendario(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $start = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $maintenances = Maintenance::with('vehicle')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->whereBetween('scheduled_date', [$start, $end])
            ->orderBy('scheduled_date')
            ->orderBy('id')
            ->get();

        $byDate = $maintenances->groupBy(fn ($m) => $m->scheduled_date?->format('Y-m-d'));

        $prev = $start->copy()->subMonth();
        $next = $start->copy()->addMonth();
        // Permitir navegar hasta 24 meses en el futuro para ver mantenimientos programados
        $maxFuture = now()->addMonths(24);
        $canNext = $next->lte($maxFuture);

        return view('mantenimientos.calendario', compact('start', 'byDate', 'prev', 'next', 'canNext'));
    }

    public function index()
    {
        if (request()->ajax()) {
            $maintenances = Maintenance::with(['vehicle', 'responsibleTechnician', 'assignedDriver'])
                ->select('maintenances.*');

            // Aplicar búsqueda global
            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $maintenances->where(function ($query) use ($search) {
                    $query->whereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('license_plate', 'like', "%{$search}%")
                                     ->orWhere('brand', 'like', "%{$search}%")
                                     ->orWhere('model', 'like', "%{$search}%");
                    })
                    ->orWhere('work_description', 'like', "%{$search}%")
                    ->orWhere('entry_reason', 'like', "%{$search}%");
                });
            }

            // Filtro por vehículo
            if (request()->has('columns') && request()->get('columns')[1]['search']['value']) {
                $vehicleId = request()->get('columns')[1]['search']['value'];
                if ($vehicleId) {
                    $maintenances->where('vehicle_id', $vehicleId);
                }
            }

            // Filtro por tipo
            if (request()->has('columns') && request()->get('columns')[2]['search']['value']) {
                $type = request()->get('columns')[2]['search']['value'];
                if ($type) {
                    $maintenances->where('type', $type);
                }
            }

            // Filtro por estado
            if (request()->has('columns') && request()->get('columns')[3]['search']['value']) {
                $status = request()->get('columns')[3]['search']['value'];
                if ($status) {
                    $maintenances->where('status', $status);
                }
            }

            return DataTables::of($maintenances)
                ->addColumn('vehicle_info', function ($maintenance) {
                    if ($maintenance->vehicle) {
                        return $maintenance->vehicle->license_plate . '<br><span class="text-xs text-gray-500 dark:text-gray-400">' 
                             . $maintenance->vehicle->brand . ' ' . $maintenance->vehicle->model . '</span>';
                    }
                    return '<span class="text-gray-500 dark:text-gray-400 italic">Vehículo eliminado</span>';
                })
                ->addColumn('type_badge', function ($maintenance) {
                    $typeColors = [
                        'preventive' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
                        'corrective' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300',
                        'inspection' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
                    ];
                    $color = $typeColors[$maintenance->type] ?? $typeColors['preventive'];
                    $typeText = __('mantenimiento.types.' . $maintenance->type, [], 'es');
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$typeText}</span>";
                })
                ->addColumn('status_badge', function ($maintenance) {
                    $statusColors = [
                        'scheduled' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                        'in_progress' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                        'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                        'pending_approval' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300',
                        'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                    ];
                    $color = $statusColors[$maintenance->status] ?? $statusColors['scheduled'];
                    $statusText = __('mantenimiento.statuses.' . $maintenance->status, [], 'es');
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$statusText}</span>";
                })
                ->addColumn('formatted_cost', function ($maintenance) {
                    return '$' . number_format($maintenance->total_cost, 0, ',', '.');
                })
                ->addColumn('formatted_date', function ($maintenance) {
                    return $maintenance->scheduled_date ? $maintenance->scheduled_date->format('d/m/Y') : 'Sin fecha';
                })
                ->addColumn('actions', function ($maintenance) {
                    $user = auth()->user();
                    $html = "<div class='flex justify-end flex-wrap gap-2 items-center'>";
                    $html .= "<a href='" . route('mantenimientos.show', $maintenance->id) . "' class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300' title='Ver'><i class='fas fa-eye'></i></a>";
                    if ($user->can('maintenances.edit')) {
                        $html .= "<a href='" . route('mantenimientos.edit', $maintenance->id) . "' class='text-blue-600 dark:text-blue-400 hover:text-blue-900' title='Editar'><i class='fas fa-edit'></i></a>";
                    }
                    if ($user->can('maintenances.record_work')
                        && (int) $maintenance->responsible_technician_id === (int) $user->id
                        && ! in_array($maintenance->status, ['completed', 'cancelled'], true)) {
                        $html .= "<a href='" . route('mantenimientos.registrar-trabajo', $maintenance) . "' class='text-amber-600 dark:text-amber-400 hover:text-amber-800 text-xs font-medium whitespace-nowrap' title='Trabajos realizados'><i class='fas fa-wrench mr-1'></i>Trabajo</a>";
                    }
                    if ($user->can('maintenances.delete')) {
                        $html .= "<button type='button' onclick='deleteMaintenance(" . $maintenance->id . ")' class='text-red-600 dark:text-red-400 hover:text-red-900 cursor-pointer bg-transparent border-none p-0' title='Eliminar'><i class='fas fa-trash-alt'></i></button>";
                    }
                    $html .= '</div>';

                    return $html;
                })
                ->rawColumns(['vehicle_info', 'type_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('mantenimientos.index');
    }

    public function approve(int $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        if ($maintenance->status !== 'pending_approval') {
            return redirect()->back()->with('error', 'Solo se pueden aprobar mantenimientos en estado pendiente de aprobación.');
        }
        if (! $maintenance->hasRequiredChecklistCompleted()) {
            return redirect()->back()->with('error', 'Debe completar todos los ítems obligatorios del checklist antes de aprobar.');
        }
        $oldStatus = $maintenance->status;
        $maintenance->update([
            'status' => 'completed',
            'approved_by_id' => auth()->id(),
            'approved_at' => now(),
        ]);
        $maintenance->processSparePartsUsage();

        $this->audit->log(
            'approve_maintenance',
            'Maintenance',
            $maintenance->id,
            'Mantenimiento #' . $maintenance->id . ' aprobado (vehículo: ' . ($maintenance->vehicle?->license_plate ?? 'N/A') . ', costo total: $' . number_format($maintenance->total_cost, 0, ',', '.') . ')',
            ['status' => $oldStatus],
            ['status' => 'completed', 'approved_by_id' => auth()->id()]
        );

        return redirect()->back()->with('success', 'Mantenimiento aprobado correctamente.');
    }

    public function pendingTasks(Request $request)
    {
        if (! $request->user()->can('maintenances.pending_tasks')) {
            abort(403);
        }

        $query = Maintenance::with(['vehicle', 'responsibleTechnician'])
            ->whereNotIn('status', ['completed', 'cancelled']);

        $isTechnicianOnly = $request->user()->hasRole('technician')
            && ! $request->user()->hasAnyRole(['administrator', 'supervisor', 'administrativo']);

        if ($isTechnicianOnly) {
            $query->where('responsible_technician_id', $request->user()->id);
        }

        $maintenances = $query
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'pending_approval' THEN 2 WHEN 'scheduled' THEN 3 ELSE 4 END")
            ->orderBy('scheduled_date')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('mantenimientos.tareas-pendientes', compact('maintenances'));
    }

    public function showRegistrarTrabajo(Request $request, Maintenance $maintenance)
    {
        $this->assertTechnicianCanRecordWork($request, $maintenance);

        $maintenance->load(['maintenanceSpareParts.sparePart', 'vehicle']);
        $spareParts = SparePart::where('active', true)->orderBy('code')->get();

        return view('mantenimientos.registrar-trabajo', compact('maintenance', 'spareParts'));
    }

    public function storeRegistrarTrabajo(Request $request, Maintenance $maintenance)
    {
        $this->assertTechnicianCanRecordWork($request, $maintenance);

        $validated = $request->validate([
            'work_performed' => ['nullable', 'string', 'max:65535'],
            'parts_cost' => ['nullable', 'integer', 'min:0'],
            'labor_cost' => ['nullable', 'integer', 'min:0'],
            'total_cost' => ['nullable', 'integer', 'min:0'],
            'hours_worked' => ['nullable', 'numeric', 'min:0'],
            'observations' => ['nullable', 'string', 'max:65535'],
            'evidence_invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'evidence_photo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $data = [
            'work_performed' => $validated['work_performed'] ?? null,
            'parts_cost' => (int) ($validated['parts_cost'] ?? 0),
            'labor_cost' => (int) ($validated['labor_cost'] ?? 0),
            'total_cost' => (int) ($validated['total_cost'] ?? 0),
            'hours_worked' => $validated['hours_worked'] ?? null,
            'observations' => $validated['observations'] ?? null,
        ];

        if ($request->hasFile('evidence_invoice')) {
            if ($maintenance->evidence_invoice_path && Storage::disk('public')->exists($maintenance->evidence_invoice_path)) {
                Storage::disk('public')->delete($maintenance->evidence_invoice_path);
            }
            $data['evidence_invoice_path'] = $request->file('evidence_invoice')->store('maintenances/' . $maintenance->id, 'public');
        }
        if ($request->hasFile('evidence_photo')) {
            if ($maintenance->evidence_photo_path && Storage::disk('public')->exists($maintenance->evidence_photo_path)) {
                Storage::disk('public')->delete($maintenance->evidence_photo_path);
            }
            $data['evidence_photo_path'] = $request->file('evidence_photo')->store('maintenances/' . $maintenance->id, 'public');
        }

        $maintenance->update($data);

        return redirect()
            ->route('mantenimientos.show', $maintenance->id)
            ->with('success', 'Datos del trabajo guardados correctamente.');
    }

    public function addSparePartTechnician(Request $request, Maintenance $maintenance)
    {
        $this->assertTechnicianCanRecordWork($request, $maintenance);

        $validated = $request->validate([
            'spare_part_id' => 'required|exists:spare_parts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $existing = MaintenanceSparePart::where('maintenance_id', $maintenance->id)
            ->where('spare_part_id', $validated['spare_part_id'])
            ->first();
        if ($existing) {
            $existing->increment('quantity', $validated['quantity']);
        } else {
            MaintenanceSparePart::create([
                'maintenance_id' => $maintenance->id,
                'spare_part_id' => $validated['spare_part_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        return redirect()
            ->route('mantenimientos.registrar-trabajo', $maintenance)
            ->with('success', 'Repuesto agregado.');
    }

    public function removeSparePartTechnician(Request $request, Maintenance $maintenance, int $pivotId)
    {
        $this->assertTechnicianCanRecordWork($request, $maintenance);

        MaintenanceSparePart::where('maintenance_id', $maintenance->id)->where('id', $pivotId)->delete();

        return redirect()
            ->route('mantenimientos.registrar-trabajo', $maintenance)
            ->with('success', 'Repuesto quitado.');
    }

    private function assertTechnicianCanRecordWork(Request $request, Maintenance $maintenance): void
    {
        if (! $request->user()->can('maintenances.record_work')) {
            abort(403);
        }
        if (in_array($maintenance->status, ['completed', 'cancelled'], true)) {
            abort(403, 'Este mantenimiento ya está cerrado.');
        }
        if ((int) $maintenance->responsible_technician_id !== (int) $request->user()->id) {
            abort(403, 'Solo el técnico asignado puede registrar los trabajos realizados.');
        }
    }

    public function destroy($id)
    {
        if (! auth()->user()->can('maintenances.delete')) {
            abort(403);
        }
        try {
            $maintenance = Maintenance::with('vehicle')->findOrFail($id);
            $vehicleInfo = $maintenance->vehicle ? $maintenance->vehicle->license_plate : 'N/A';
            $this->audit->log(
                'delete_maintenance',
                'Maintenance',
                $maintenance->id,
                'Mantenimiento #' . $maintenance->id . ' eliminado (vehículo: ' . $vehicleInfo . ', tipo: ' . $maintenance->type . ')',
                $maintenance->toArray(),
                null
            );
            $maintenance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el mantenimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addSparePart(Request $request, int $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        if ($maintenance->status === 'completed' || $maintenance->status === 'cancelled') {
            return redirect()->back()->with('error', 'No se pueden agregar repuestos a un mantenimiento completado o cancelado.');
        }

        $validated = $request->validate([
            'spare_part_id' => 'required|exists:spare_parts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $existing = MaintenanceSparePart::where('maintenance_id', $id)
            ->where('spare_part_id', $validated['spare_part_id'])
            ->first();
        if ($existing) {
            $existing->increment('quantity', $validated['quantity']);
        } else {
            MaintenanceSparePart::create([
                'maintenance_id' => $id,
                'spare_part_id' => $validated['spare_part_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        return redirect()->back()->with('success', 'Repuesto agregado al mantenimiento.');
    }

    public function removeSparePart(int $id, int $pivotId)
    {
        $maintenance = Maintenance::findOrFail($id);
        if ($maintenance->status === 'completed' || $maintenance->status === 'cancelled') {
            return redirect()->back()->with('error', 'No se pueden quitar repuestos de un mantenimiento completado o cancelado.');
        }

        MaintenanceSparePart::where('maintenance_id', $id)->where('id', $pivotId)->delete();

        return redirect()->back()->with('success', 'Repuesto quitado del mantenimiento.');
    }

    public function toggleChecklistItem(int $id, int $itemId)
    {
        $maintenance = Maintenance::findOrFail($id);
        if ($maintenance->status === 'completed' || $maintenance->status === 'cancelled') {
            return redirect()->back()->with('error', 'No se puede modificar el checklist de un mantenimiento completado o cancelado.');
        }

        $item = MaintenanceChecklistItem::findOrFail($itemId);
        if (! $item->appliesToType($maintenance->type)) {
            return redirect()->back()->with('error', 'Ese ítem no aplica al tipo de este mantenimiento.');
        }

        $completion = MaintenanceChecklistCompletion::where('maintenance_id', $id)
            ->where('maintenance_checklist_item_id', $itemId)
            ->first();

        if ($completion) {
            $completion->delete();
            return redirect()->back()->with('success', 'Ítem desmarcado.');
        }

        MaintenanceChecklistCompletion::create([
            'maintenance_id' => $id,
            'maintenance_checklist_item_id' => $itemId,
            'completed_at' => now(),
            'completed_by_id' => auth()->id(),
        ]);
        return redirect()->back()->with('success', 'Ítem marcado como completado.');
    }

    public function export(Request $request, $format)
    {
        $filters = [
            'vehicle_id' => $request->get('vehicle_id'),
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
        ];

        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $filename = 'mantenimientos_' . date('Y-m-d_His');

        switch ($format) {
            case 'excel':
                return Excel::download(new MaintenancesExport($filters), $filename . '.xlsx');
            case 'csv':
                return Excel::download(new MaintenancesExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
            default:
                return redirect()->back()->with('error', 'Formato de exportación no válido');
        }
    }
}
