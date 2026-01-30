<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Vehicle;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Estadísticas básicas
        $stats = [
            'vehiculos_total' => Vehicle::count(),
            'vehiculos_activos' => Vehicle::where('status', 'active')->count(),
            'vehiculos_mantenimiento' => Vehicle::where('status', 'maintenance')->count(),
            'vehiculos_inactivos' => Vehicle::where('status', 'inactive')->count(),
            'mantenimientos_programados' => Maintenance::where('status', 'scheduled')->count(),
            'mantenimientos_en_proceso' => Maintenance::where('status', 'in_progress')->count(),
            'mantenimientos_completados_mes' => Maintenance::where('status', 'completed')
                ->whereMonth('end_date', now()->month)
                ->whereYear('end_date', now()->year)
                ->count(),
            'alertas_activas' => Alert::where('status', '!=', 'closed')->count(),
            'alertas_criticas' => Alert::where('status', '!=', 'closed')->where('severity', 'critica')->count(),
        ];

        // Costos del mes actual
        $costo_mes_actual = Maintenance::where('status', 'completed')
            ->whereMonth('end_date', now()->month)
            ->whereYear('end_date', now()->year)
            ->sum('total_cost');

        // Costos últimos 6 meses para gráfico
        $costos_mensuales = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mes_nombre = $fecha->format('M Y');
            $costo = Maintenance::where('status', 'completed')
                ->whereMonth('end_date', $fecha->month)
                ->whereYear('end_date', $fecha->year)
                ->sum('total_cost');
            
            $costos_mensuales[] = [
                'mes' => $mes_nombre,
                'costo' => (int) $costo
            ];
        }

        // Mantenimientos en curso con detalles
        $mantenimientos_en_curso = Maintenance::with(['vehicle', 'responsibleTechnician'])
            ->where('status', 'in_progress')
            ->orderBy('start_date', 'asc')
            ->limit(10)
            ->get();

        // Próximos mantenimientos (programados)
        $proximos_mantenimientos = Maintenance::with('vehicle')
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', now())
            ->orderBy('scheduled_date', 'asc')
            ->limit(10)
            ->get();

        // Mantenimientos recientes completados
        $mantenimientos_recientes = Maintenance::with('vehicle')
            ->where('status', 'completed')
            ->orderBy('end_date', 'desc')
            ->limit(5)
            ->get();

        // Vehículos que requieren atención (con mantenimientos vencidos o próximos)
        $vehiculos_atencion = Vehicle::whereHas('maintenances', function($query) {
            $query->where('status', 'scheduled')
                  ->where('scheduled_date', '<=', now()->addDays(7));
        })
        ->orWhere('status', 'maintenance')
        ->with(['maintenances' => function($query) {
            $query->where('status', 'scheduled')
                  ->where('scheduled_date', '<=', now()->addDays(7))
                  ->orderBy('scheduled_date', 'asc');
        }])
        ->limit(10)
        ->get();

        // Top 5 vehículos por costo (últimos 6 meses)
        $top_vehiculos_costo = Vehicle::withSum(['maintenances' => function($query) {
            $query->where('status', 'completed')
                  ->where('end_date', '>=', now()->subMonths(6));
        }], 'total_cost')
        ->orderBy('maintenances_sum_total_cost', 'desc')
        ->limit(5)
        ->get();

        // Estadísticas por tipo de mantenimiento
        $stats_por_tipo = [
            'preventive' => Maintenance::where('status', 'completed')
                ->where('type', 'preventive')
                ->where('end_date', '>=', now()->subMonths(6))
                ->sum('total_cost'),
            'corrective' => Maintenance::where('status', 'completed')
                ->where('type', 'corrective')
                ->where('end_date', '>=', now()->subMonths(6))
                ->sum('total_cost'),
            'inspection' => Maintenance::where('status', 'completed')
                ->where('type', 'inspection')
                ->where('end_date', '>=', now()->subMonths(6))
                ->sum('total_cost'),
        ];

        // Alertas activas (para listado en dashboard; incluye alertas de vehículo y de stock)
        $alertas_activas = Alert::with(['vehicle', 'sparePart'])
            ->where('status', '!=', 'closed')
            ->orderByRaw("CASE severity WHEN 'critica' THEN 1 WHEN 'advertencia' THEN 2 ELSE 3 END")
            ->orderBy('due_date', 'asc')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'costo_mes_actual',
            'costos_mensuales',
            'mantenimientos_en_curso',
            'proximos_mantenimientos',
            'mantenimientos_recientes',
            'vehiculos_atencion',
            'top_vehiculos_costo',
            'stats_por_tipo',
            'alertas_activas'
        ));
    }
}
