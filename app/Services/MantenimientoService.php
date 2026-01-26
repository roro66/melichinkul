<?php

namespace App\Services;

use App\Models\Mantenimiento;
use Illuminate\Validation\ValidationException;

class MantenimientoService
{
    /**
     * Verifica si el mantenimiento puede cerrarse (estado completado).
     * Plan 13.3: no se puede cerrar un correctivo sin factura y/o foto del repuesto instalado.
     *
     * @throws ValidationException Si es correctivo y no tiene evidencia (evidencia_factura o evidencia_foto).
     */
    public function validarCierre(Mantenimiento $mantenimiento): void
    {
        if (! $mantenimiento->esCorrectivo()) {
            return;
        }

        if (! $mantenimiento->tieneEvidenciaObligatoria()) {
            throw ValidationException::withMessages([
                'evidencia' => [__('mantenimiento.cierre_sin_evidencia')],
            ]);
        }
    }

    /**
     * Indica si el mantenimiento puede cerrarse sin lanzar excepción.
     */
    public function puedeCerrar(Mantenimiento $mantenimiento): bool
    {
        if (! $mantenimiento->esCorrectivo()) {
            return true;
        }

        return $mantenimiento->tieneEvidenciaObligatoria();
    }

    /**
     * Cierra el mantenimiento (estado completado) validando evidencia en correctivos.
     *
     * @param  array<string, mixed>  $datos  Campos a actualizar (fecha_fin, trabajos_realizados, costos, etc.)
     * @throws ValidationException Si es correctivo sin evidencia.
     */
    public function cerrar(Mantenimiento $mantenimiento, array $datos): void
    {
        $this->validarCierre($mantenimiento);

        $mantenimiento->update(array_merge($datos, ['estado' => 'completado']));
    }
}
