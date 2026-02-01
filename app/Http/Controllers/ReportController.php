<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Driver;
use App\Models\InventoryMovement;
use App\Models\Maintenance;
use App\Models\Purchase;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Período por defecto para reportes: últimos 12 meses.
     */
    private function periodStart(): Carbon
    {
        return now()->subMonths(12)->startOfMonth();
    }

    /**
     * Mantenimientos correctivos (fallas) completados en el período.
     */
    private function correctiveQuery()
    {
        return Maintenance::where('type', 'corrective')
            ->where('status', 'completed')
            ->where('end_date', '>=', $this->periodStart());
    }

    public function index(Request $request)
    {
        $start = $this->periodStart();

        // --- Fallas por vehículo (cantidad y costo total) ---
        $fallasPorVehiculo = Vehicle::query()
            ->select('vehicles.id', 'vehicles.license_plate', 'vehicles.brand', 'vehicles.model')
            ->selectRaw('COUNT(m.id) as fallas_count')
            ->selectRaw('COALESCE(SUM(m.total_cost), 0) as fallas_costo')
            ->leftJoin('maintenances as m', function ($join) use ($start) {
                $join->on('vehicles.id', '=', 'm.vehicle_id')
                    ->where('m.type', '=', 'corrective')
                    ->where('m.status', '=', 'completed')
                    ->where('m.end_date', '>=', $start)
                    ->whereNull('m.deleted_at');
            })
            ->groupBy('vehicles.id', 'vehicles.license_plate', 'vehicles.brand', 'vehicles.model')
            ->orderByDesc('fallas_count')
            ->get();

        // --- Fallas por conductor (asignado al mantenimiento) ---
        $fallasPorConductor = Driver::query()
            ->select('drivers.id', 'drivers.full_name', 'drivers.rut')
            ->selectRaw('COUNT(m.id) as fallas_count')
            ->selectRaw('COALESCE(SUM(m.total_cost), 0) as fallas_costo')
            ->leftJoin('maintenances as m', function ($join) use ($start) {
                $join->on('drivers.id', '=', 'm.assigned_driver_id')
                    ->where('m.type', '=', 'corrective')
                    ->where('m.status', '=', 'completed')
                    ->where('m.end_date', '>=', $start)
                    ->whereNull('m.deleted_at');
            })
            ->groupBy('drivers.id', 'drivers.full_name', 'drivers.rut')
            ->orderByDesc('fallas_count')
            ->get();

        // --- Tendencia de costos: últimos 12 meses (total y por tipo) ---
        $costosMensuales = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mesLabel = $fecha->locale('es')->translatedFormat('M Y');
            $base = Maintenance::where('status', 'completed')
                ->whereMonth('end_date', $fecha->month)
                ->whereYear('end_date', $fecha->year);

            $costosMensuales[] = [
                'mes' => $mesLabel,
                'costo_total' => (int) (clone $base)->sum('total_cost'),
                'preventivo' => (int) (clone $base)->where('type', 'preventive')->sum('total_cost'),
                'correctivo' => (int) (clone $base)->where('type', 'corrective')->sum('total_cost'),
                'inspeccion' => (int) (clone $base)->where('type', 'inspection')->sum('total_cost'),
            ];
        }

        // --- Distribución por tipo (cantidad y costo en el período) ---
        $distribucionTipo = [
            'preventive' => [
                'count' => Maintenance::where('status', 'completed')->where('type', 'preventive')->where('end_date', '>=', $start)->count(),
                'costo' => Maintenance::where('status', 'completed')->where('type', 'preventive')->where('end_date', '>=', $start)->sum('total_cost'),
            ],
            'corrective' => [
                'count' => Maintenance::where('status', 'completed')->where('type', 'corrective')->where('end_date', '>=', $start)->count(),
                'costo' => Maintenance::where('status', 'completed')->where('type', 'corrective')->where('end_date', '>=', $start)->sum('total_cost'),
            ],
            'inspection' => [
                'count' => Maintenance::where('status', 'completed')->where('type', 'inspection')->where('end_date', '>=', $start)->count(),
                'costo' => Maintenance::where('status', 'completed')->where('type', 'inspection')->where('end_date', '>=', $start)->sum('total_cost'),
            ],
        ];

        // --- Top vehículos por costo total (período) ---
        $topVehiculosCosto = Vehicle::withSum(['maintenances' => function ($q) use ($start) {
            $q->where('status', 'completed')->where('end_date', '>=', $start);
        }], 'total_cost')
            ->orderByDesc('maintenances_sum_total_cost')
            ->limit(10)
            ->get();

        // --- Reportes inventario/compras: compras por proveedor (período) ---
        $comprasPorProveedor = DB::table('suppliers as s')
            ->leftJoin('purchases as p', function ($join) use ($start) {
                $join->on('p.supplier_id', '=', 's.id')
                    ->where('p.status', '=', Purchase::STATUS_RECEIVED)
                    ->where('p.purchase_date', '>=', $start);
            })
            ->leftJoin('purchase_items as pi', 'pi.purchase_id', '=', 'p.id')
            ->select('s.id', 's.name')
            ->selectRaw('COUNT(DISTINCT p.id) as compras_count')
            ->selectRaw('COALESCE(SUM(pi.quantity * pi.unit_price), 0) as total_amount')
            ->groupBy('s.id', 's.name')
            ->orderByDesc('total_amount')
            ->get();

        // --- Compras por mes (tendencia últimos 12 meses) ---
        $comprasMensuales = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mesLabel = $fecha->locale('es')->translatedFormat('M Y');
            $amount = (int) DB::table('purchases as p')
                ->join('purchase_items as pi', 'pi.purchase_id', '=', 'p.id')
                ->where('p.status', Purchase::STATUS_RECEIVED)
                ->whereMonth('p.purchase_date', $fecha->month)
                ->whereYear('p.purchase_date', $fecha->year)
                ->sum(DB::raw('pi.quantity * pi.unit_price'));
            $comprasMensuales[] = ['mes' => $mesLabel, 'monto' => $amount];
        }

        // --- Movimientos de inventario por tipo (período) ---
        $movimientosPorTipo = InventoryMovement::query()
            ->where('movement_date', '>=', $start)
            ->select('type')
            ->selectRaw('COUNT(*) as movimientos_count')
            ->selectRaw('SUM(quantity) as cantidad_neto')
            ->groupBy('type')
            ->orderByDesc('movimientos_count')
            ->get();

        // --- Top conductores por costo total (correctivos, período) ---
        $topConductoresCosto = Driver::withCount(['maintenances as fallas_count' => function ($q) use ($start) {
            $q->where('type', 'corrective')->where('status', 'completed')->where('end_date', '>=', $start);
        }])
            ->withSum(['maintenances as maintenances_sum_total_cost' => function ($q) use ($start) {
                $q->where('type', 'corrective')->where('status', 'completed')->where('end_date', '>=', $start);
            }], 'total_cost')
            ->orderByDesc('maintenances_sum_total_cost')
            ->limit(10)
            ->get();

        // --- Tendencia costos correctivos por conductor (top 5, últimos 12 meses) ---
        $conductoresTop5 = $fallasPorConductor->where('fallas_count', '>', 0)->take(5);
        $conductoresTopIds = $conductoresTop5->pluck('id')->all();
        $conductoresTopNombres = $conductoresTop5->pluck('full_name', 'id')->all();
        $costosConductorMensuales = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mesLabel = $fecha->locale('es')->translatedFormat('M Y');
            $row = ['mes' => $mesLabel];
            foreach ($conductoresTopIds as $driverId) {
                $costo = (int) Maintenance::where('assigned_driver_id', $driverId)
                    ->where('type', 'corrective')
                    ->where('status', 'completed')
                    ->whereMonth('end_date', $fecha->month)
                    ->whereYear('end_date', $fecha->year)
                    ->sum('total_cost');
                $row['driver_' . $driverId] = $costo;
            }
            $costosConductorMensuales[] = $row;
        }

        // --- Resumen numérico ---
        $resumen = [
            'total_fallas' => (int) $this->correctiveQuery()->count(),
            'costo_fallas' => (int) $this->correctiveQuery()->sum('total_cost'),
            'total_mantenimientos' => Maintenance::where('status', 'completed')->where('end_date', '>=', $start)->count(),
            'costo_total_periodo' => (int) Maintenance::where('status', 'completed')->where('end_date', '>=', $start)->sum('total_cost'),
        ];

        $resumenInventario = [
            'total_compras_periodo' => (int) DB::table('purchases as p')
                ->join('purchase_items as pi', 'pi.purchase_id', '=', 'p.id')
                ->where('p.status', Purchase::STATUS_RECEIVED)
                ->where('p.purchase_date', '>=', $start)
                ->sum(DB::raw('pi.quantity * pi.unit_price')),
            'movimientos_count' => (int) InventoryMovement::where('movement_date', '>=', $start)->count(),
        ];

        return view('reportes.index', compact(
            'fallasPorVehiculo',
            'fallasPorConductor',
            'costosMensuales',
            'distribucionTipo',
            'topVehiculosCosto',
            'comprasPorProveedor',
            'comprasMensuales',
            'movimientosPorTipo',
            'topConductoresCosto',
            'costosConductorMensuales',
            'conductoresTopIds',
            'conductoresTopNombres',
            'resumen',
            'resumenInventario',
            'start'
        ));
    }

    public function estadoFlotaPdf()
    {
        $stats = $this->dashboardStats();
        $pdf = Pdf::loadView('pdf.estado-flota', compact('stats'));
        return $pdf->download('estado-flota-' . now()->format('Y-m-d') . '.pdf');
    }

    public function dashboardEjecutivoPdf()
    {
        $stats = $this->dashboardStats();
        $costo_mes = Maintenance::where('status', 'completed')
            ->whereMonth('end_date', now()->month)
            ->whereYear('end_date', now()->year)
            ->sum('total_cost');
        $pdf = Pdf::loadView('pdf.dashboard-ejecutivo', compact('stats', 'costo_mes'));
        return $pdf->download('dashboard-ejecutivo-' . now()->format('Y-m-d') . '.pdf');
    }

    private function dashboardStats(): array
    {
        return [
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
    }
}
