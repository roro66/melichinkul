<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'vehiculos_total' => Vehicle::count(),
            'vehiculos_activos' => Vehicle::where('status', 'active')->count(),
            'mantenimientos_programados' => Maintenance::where('status', 'scheduled')->count(),
            'mantenimientos_en_proceso' => Maintenance::where('status', 'in_progress')->count(),
        ];

        $mantenimientos_recientes = Maintenance::with('vehicle')
            ->whereIn('status', ['in_progress', 'completed'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'mantenimientos_recientes'));
    }
}
