<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Repuesto;
use Illuminate\Support\Facades\DB;

class StockCriticoService
{
    /**
     * Días de historial mínimos para usar umbral dinámico.
     */
    public const UMBRAL_DIAS_HISTORIAL = 90;

    /**
     * Multiplicador sobre el promedio de consumo mensual para stock crítico dinámico.
     */
    public const MULTIPLICADOR_DINAMICO = 1.5;

    /**
     * Devuelve el stock crítico: manual o dinámico si hay ≥90 días de movimientos.
     */
    public function stockCriticoPara(Repuesto $repuesto): int|float
    {
        if ($this->usaDinamicoPara($repuesto)) {
            return $this->calcularStockCriticoDinamico($repuesto);
        }

        return $repuesto->stock_minimo_manual;
    }

    /**
     * True si el repuesto tiene ≥90 días de historial de movimientos de salida.
     */
    public function usaDinamicoPara(Repuesto $repuesto): bool
    {
        $primerMovimiento = MovimientoInventario::where('repuesto_id', $repuesto->id)
            ->whereIn('tipo', ['salida_mantenimiento', 'salida_ajuste', 'salida_vencimiento'])
            ->orderBy('fecha')
            ->value('fecha');

        if (! $primerMovimiento) {
            return false;
        }

        $dias = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($primerMovimiento)->startOfDay(), true);

        return $dias >= self::UMBRAL_DIAS_HISTORIAL;
    }

    /**
     * (Promedio consumo mensual últimos 3 meses) × 1.5
     * Consumo = suma de cantidades de movimientos de salida.
     */
    protected function calcularStockCriticoDinamico(Repuesto $repuesto): float
    {
        $haceTresMeses = now()->subMonths(3)->startOfDay();

        $totalSalidas = MovimientoInventario::where('repuesto_id', $repuesto->id)
            ->whereIn('tipo', ['salida_mantenimiento', 'salida_ajuste', 'salida_vencimiento'])
            ->where('fecha', '>=', $haceTresMeses)
            ->sum(DB::raw('ABS(cantidad)'));

        $promedioMensual = $totalSalidas / 3;

        return (float) round($promedioMensual * self::MULTIPLICADOR_DINAMICO, 2);
    }
}
