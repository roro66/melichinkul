<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Mantenimiento;
use App\Models\Alerta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'vehiculos_total' => Vehiculo::count(),
            'vehiculos_activos' => Vehiculo::where('estado', 'activo')->count(),
            'mantenimientos_programados' => Mantenimiento::where('estado', 'programado')->count(),
            'mantenimientos_en_proceso' => Mantenimiento::where('estado', 'en_proceso')->count(),
            'alertas_criticas' => Alerta::where('severidad', 'critica')
                ->where('estado', '!=', 'cerrada')
                ->vigentes()
                ->count(),
            'alertas_pendientes' => Alerta::where('estado', 'pendiente')
                ->vigentes()
                ->count(),
        ];

        $alertas_recientes = Alerta::with(['vehiculo', 'conductor', 'certificacion'])
            ->where('estado', '!=', 'cerrada')
            ->vigentes()
            ->orderBy('fecha_generada', 'desc')
            ->limit(10)
            ->get();

        $mantenimientos_recientes = Mantenimiento::with('vehiculo')
            ->whereIn('estado', ['en_proceso', 'completado'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'alertas_recientes', 'mantenimientos_recientes'));
    }
}
