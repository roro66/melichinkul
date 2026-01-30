<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AlertController extends Controller
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            $alerts = Alert::with(['vehicle', 'closedBy', 'snoozedBy'])
                ->select('alerts.*')
                ->where('status', '!=', 'closed');

            if (request()->has('search') && request()->get('search')['value']) {
                $search = request()->get('search')['value'];
                $alerts->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', function ($q) use ($search) {
                            $q->where('license_plate', 'like', "%{$search}%")
                                ->orWhere('brand', 'like', "%{$search}%")
                                ->orWhere('model', 'like', "%{$search}%");
                        });
                });
            }

            $severityFilter = request()->input('columns.2.search.value');
            if ($severityFilter) {
                $alerts->where('severity', $severityFilter);
            }

            $vehicleFilter = request()->input('columns.1.search.value');
            if ($vehicleFilter) {
                $alerts->where('vehicle_id', $vehicleFilter);
            }

            return DataTables::of($alerts)
                ->addColumn('generated_at', function (Alert $alert) {
                    return $alert->generated_at ? $alert->generated_at->format('d/m/Y H:i') : '—';
                })
                ->addColumn('vehicle_info', function (Alert $alert) {
                    if ($alert->vehicle) {
                        return $alert->vehicle->license_plate . '<br><span class="text-xs text-gray-500 dark:text-gray-400">'
                            . $alert->vehicle->brand . ' ' . $alert->vehicle->model . '</span>';
                    }
                    return '<span class="text-gray-500 dark:text-gray-400 italic">—</span>';
                })
                ->addColumn('severity_badge', function (Alert $alert) {
                    $colors = [
                        'informativa' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                        'advertencia' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                        'critica' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                    ];
                    $color = $colors[$alert->severity] ?? $colors['informativa'];
                    $label = ucfirst($alert->severity);
                    return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$color}'>{$label}</span>";
                })
                ->addColumn('due_date_formatted', function (Alert $alert) {
                    return $alert->due_date ? $alert->due_date->format('d/m/Y') : '—';
                })
                ->addColumn('snoozed_info', function (Alert $alert) {
                    if ($alert->snoozed_until && $alert->snoozed_until->isFuture()) {
                        return '<span class="text-amber-600 dark:text-amber-400 text-xs" title="' . $alert->snoozed_reason . '">Pospuesta hasta ' . $alert->snoozed_until->format('d/m/Y H:i') . '</span>';
                    }
                    return '—';
                })
                ->addColumn('actions', function (Alert $alert) {
                    $closeUrl = route('alerts.close', $alert->id);
                    $snoozeUrl = route('alerts.snooze', $alert->id);
                    $viewVehicle = route('vehiculos.show', $alert->vehicle_id);
                    $buttons = "
                        <div class='flex justify-end items-center gap-2'>
                            <a href='{$viewVehicle}' class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300' title='Ver vehículo'>
                                <i class='fas fa-car'></i>
                            </a>
                            <button type='button' onclick='snoozeAlert({$alert->id})' class='text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 cursor-pointer bg-transparent border-none' title='Posponer'>
                                <i class='fas fa-clock'></i>
                            </button>
                            <button type='button' onclick='closeAlert({$alert->id})' class='text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 cursor-pointer bg-transparent border-none' title='Cerrar'>
                                <i class='fas fa-check-circle'></i>
                            </button>
                        </div>
                    ";
                    return $buttons;
                })
                ->rawColumns(['vehicle_info', 'severity_badge', 'snoozed_info', 'actions'])
                ->make(true);
        }

        return view('alerts.index');
    }

    public function close(int $id)
    {
        $alert = Alert::findOrFail($id);
        if ($alert->status === 'closed') {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => __('alerta.snooze_cerrada')], 422);
            }
            return redirect()->back()->with('error', __('alerta.snooze_cerrada'));
        }
        $alert->update([
            'status' => 'closed',
            'closed_by_id' => auth()->id(),
            'closed_at' => now(),
        ]);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Alerta cerrada correctamente.']);
        }
        return redirect()->back()->with('success', 'Alerta cerrada correctamente.');
    }

    public function snooze(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'hours' => 'nullable|integer|min:48|max:72',
        ]);
        $alert = Alert::findOrFail($id);
        try {
            $this->alertService->snooze(
                $alert,
                auth()->user(),
                $request->input('reason'),
                (int) ($request->input('hours', AlertService::SNOOZE_MIN_HOURS))
            );
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Alerta pospuesta correctamente.']);
            }
            return redirect()->back()->with('success', 'Alerta pospuesta correctamente.');
        } catch (\InvalidArgumentException $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
