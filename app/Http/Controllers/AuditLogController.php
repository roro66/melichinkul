<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $logs = AuditLog::with('user')->select('audit_logs.*')->orderByDesc('created_at');

            if ($request->filled('user_id')) {
                $logs->where('user_id', $request->user_id);
            }
            if ($request->filled('model')) {
                $logs->where('model', $request->model);
            }
            if ($request->filled('action')) {
                $logs->where('action', 'like', '%' . $request->action . '%');
            }

            return DataTables::of($logs)
                ->addColumn('user_name', function (AuditLog $log) {
                    return $log->user ? $log->user->name : '—';
                })
                ->addColumn('created_at_formatted', function (AuditLog $log) {
                    return $log->created_at?->format('d/m/Y H:i:s') ?? '—';
                })
                ->addColumn('action_label', function (AuditLog $log) {
                    $labels = [
                        'delete_vehicle' => 'Eliminar vehículo',
                        'create_maintenance' => 'Crear mantenimiento',
                        'update_maintenance' => 'Editar mantenimiento',
                        'sync_maintenance_purchase_items' => 'Sincronizar repuestos/compras (modal)',
                        'record_work_maintenance' => 'Registrar trabajo (técnico)',
                        'add_maintenance_spare_part' => 'Agregar repuesto de bodega',
                        'remove_maintenance_spare_part' => 'Quitar repuesto de bodega',
                        'check_maintenance_checklist' => 'Marcar ítem checklist',
                        'uncheck_maintenance_checklist' => 'Desmarcar ítem checklist',
                        'approve_maintenance' => 'Aprobar mantenimiento',
                        'delete_maintenance' => 'Eliminar mantenimiento',
                        'close_alert' => 'Cerrar alerta',
                    ];
                    return $labels[$log->action] ?? $log->action;
                })
                ->make(true);
        }

        return view('audit.index');
    }
}
