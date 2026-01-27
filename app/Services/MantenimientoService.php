<?php

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Validation\ValidationException;

class MaintenanceService
{
    /**
     * Verifica si el mantenimiento puede cerrarse (estado completado).
     * Plan 13.3: no se puede cerrar un correctivo sin factura y/o foto del repuesto instalado.
     *
     * @throws ValidationException Si es correctivo y no tiene evidencia (evidencia_factura o evidencia_foto).
     */
    public function validarCierre(Maintenance $maintenance): void
    {
        if (! $maintenance->isCorrective()) {
            return;
        }

        if (! $maintenance->hasRequiredEvidence()) {
            throw ValidationException::withMessages([
                'evidencia' => [__('mantenimiento.cierre_sin_evidencia')],
            ]);
        }
    }

    /**
     * Indica si el mantenimiento puede cerrarse sin lanzar excepción.
     */
    public function puedeCerrar(Maintenance $maintenance): bool
    {
        if (! $maintenance->isCorrective()) {
            return true;
        }

        return $maintenance->hasRequiredEvidence();
    }

    /**
     * Cierra el mantenimiento (estado completado) validando evidencia en correctivos.
     *
     * @param  array<string, mixed>  $datos  Campos a actualizar (end_date, work_performed, costos, etc.)
     * @throws ValidationException Si es correctivo sin evidencia.
     */
    public function cerrar(Maintenance $maintenance, array $datos): void
    {
        $this->validarCierre($maintenance);

        $maintenance->update(array_merge($datos, ['status' => 'completed']));
    }
}
