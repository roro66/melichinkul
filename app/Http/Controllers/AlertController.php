<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\AlertService;
use App\Services\AuditService;
use App\Services\LicenseAlertService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AlertController extends Controller
{
    public function __construct(
        protected AlertService $alertService,
        protected AuditService $audit,
        protected LicenseAlertService $licenseAlerts
    ) {}

    /**
     * Resumen de alertas pendientes para badge en navegación (polling).
     */
    public function summary()
    {
        $total = Alert::where('status', '!=', 'closed')->count();
        $criticas = Alert::where('status', '!=', 'closed')->where('severity', 'critica')->count();

        return response()->json([
            'total' => $total,
            'criticas' => $criticas,
        ]);
    }

    public function index()
    {
        if (request()->ajax()) {
            $alerts = Alert::with(['vehicle', 'sparePart', 'closedBy', 'snoozedBy'])
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
                        })
                        ->orWhereHas('sparePart', function ($q) use ($search) {
                            $q->where('code', 'like', "%{$search}%")
                                ->orWhere('description', 'like', "%{$search}%");
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
                    if ($alert->sparePart) {
                        return '<span class="text-indigo-600 dark:text-indigo-400">' . e($alert->sparePart->code) . '</span><br><span class="text-xs text-gray-500 dark:text-gray-400">' . e($alert->sparePart->description) . '</span>';
                    }
                    if ($alert->isLicenseAlert()) {
                        $name = e($alert->metadata['driver_name'] ?? 'Conductor');

                        return $name . '<br><span class="text-xs text-gray-500 dark:text-gray-400">Sin móvil asignado</span>';
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
                    $viewLink = $alert->vehicle_id
                        ? route('vehiculos.show', $alert->vehicle_id)
                        : ($alert->spare_part_id ? route('repuestos.show', $alert->spare_part_id) : '#');
                    $viewTitle = $alert->vehicle_id ? 'Ver vehículo' : ($alert->isLicenseAlert() ? 'Licencia de conductor' : 'Ver repuesto');
                    $viewIcon = $alert->vehicle_id ? 'fa-car' : ($alert->isLicenseAlert() ? 'fa-id-card' : 'fa-box');
                    $driverId = $alert->metadata['driver_id'] ?? null;
                    if ($alert->isLicenseAlert() && $driverId) {
                        $viewLink = route('conductores.edit', $driverId);
                        $viewTitle = 'Subir licencia vigente';
                        $viewIcon = 'fa-upload';
                    }
                    $closeButton = $alert->isLicenseAlert()
                        ? ''
                        : "<button type='button' onclick='closeAlert({$alert->id})' class='text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 cursor-pointer bg-transparent border-none' title='Cerrar'><i class='fas fa-check-circle'></i></button>";
                    return "
                        <div class='flex justify-end items-center gap-2'>
                            <a href='{$viewLink}' class='text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300' title='{$viewTitle}'>
                                <i class='fas {$viewIcon}'></i>
                            </a>
                            <button type='button' onclick='snoozeAlert({$alert->id}, \"{$alert->type}\")' class='text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 cursor-pointer bg-transparent border-none' title='Posponer'>
                                <i class='fas fa-clock'></i>
                            </button>
                            {$closeButton}
                        </div>
                    ";
                })
                ->rawColumns(['vehicle_info', 'severity_badge', 'snoozed_info', 'actions'])
                ->make(true);
        }

        return view('alerts.index');
    }

    public function close(int $id)
    {
        $alert = Alert::findOrFail($id);
        if ($alert->isLicenseAlert()) {
            $message = 'La alerta de licencia se cierra al cargar la licencia vigente en la ficha del conductor.';
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }
        if ($alert->status === 'closed') {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => __('alerta.snooze_cerrada')], 422);
            }
            return redirect()->back()->with('error', __('alerta.snooze_cerrada'));
        }
        $oldStatus = $alert->status;
        $alert->update([
            'status' => 'closed',
            'closed_by_id' => auth()->id(),
            'closed_at' => now(),
        ]);

        $subject = $alert->vehicle_id
            ? ($alert->vehicle?->license_plate ?? 'Vehículo #' . $alert->vehicle_id)
            : ($alert->sparePart?->code ?? 'Repuesto #' . $alert->spare_part_id);
        $this->audit->log(
            'close_alert',
            'Alert',
            $alert->id,
            'Alerta cerrada: ' . $alert->title . ' (' . $subject . ', severidad: ' . $alert->severity . ')',
            ['status' => $oldStatus],
            ['status' => 'closed', 'closed_by_id' => auth()->id()]
        );

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Alerta cerrada correctamente.']);
        }
        return redirect()->back()->with('success', 'Alerta cerrada correctamente.');
    }

    public function licenseScreenAlert()
    {
        if (! auth()->user()->isAdministrator()) {
            return response()->json(['show' => false]);
        }

        $slot = $this->licenseAlerts->currentBusinessSlot();
        $pending = $slot === null ? collect() : $this->licenseAlerts->pendingReminders();
        if ($slot === null || $pending->isEmpty()) {
            return response()->json(['show' => false]);
        }

        return response()->json([
            'show' => true,
            'slot' => $slot,
            'reminder_kind' => 'license',
            'title' => 'Licencias de conducir por vencer',
            'message' => 'Hay licencias que requieren atención. Este aviso aparece en horario laboral hasta que un administrador lo aplace (máximo una semana) o se suba la licencia vigente.',
            'items' => $this->licenseAlerts->reminderItems($pending),
            'url' => route('alerts.index'),
        ]);
    }

    public function snooze(Request $request, int $id)
    {
        $alert = Alert::findOrFail($id);
        $license = $alert->isLicenseAlert();
        if ($license) {
            $data = $request->validate([
                'reason' => ['required', 'string', 'max:500'],
                'days' => ['required', 'integer', 'min:1', 'max:7'],
            ], [
                'days.max' => 'El aplazamiento de una licencia no puede superar una semana.',
                'days.min' => 'El aplazamiento debe ser de al menos un día.',
            ]);
            $hours = ((int) $data['days']) * 24;
            $reason = $data['reason'];
            $driverId = (int) ($alert->metadata['driver_id'] ?? 0);
            $targets = $this->licenseAlerts->openForDriver($driverId);
            if ($targets->isEmpty()) {
                $targets = collect([$alert]);
            }
        } else {
            $data = $request->validate([
                'reason' => ['required', 'string', 'max:500'],
                'hours' => ['nullable', 'integer', 'min:48', 'max:72'],
            ]);
            $hours = (int) ($data['hours'] ?? AlertService::SNOOZE_MIN_HOURS);
            $reason = $data['reason'];
            $targets = collect([$alert]);
        }

        try {
            foreach ($targets as $target) {
                $this->alertService->snooze($target, auth()->user(), $reason, $hours, $license);
            }
            $message = $license
                ? 'Alerta pospuesta. Si la licencia sigue vencida o por vencer, el aviso vuelve al terminar el plazo.'
                : 'Alerta pospuesta correctamente.';
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->back()->with('success', $message);
        } catch (\InvalidArgumentException $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
