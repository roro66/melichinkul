<?php

namespace App\Services;

use App\Models\Conductor;
use App\Models\Vehiculo;
use Illuminate\Validation\ValidationException;

/**
 * Plan 13.4: Impide asignar vehículo a conductor si:
 * 1. La licencia de conducir del conductor está vencida.
 * 2. El vehículo tiene la Revisión Técnica caducada.
 *
 * La asignación no debe persistirse; se muestra un mensaje claro al usuario.
 */
class BloqueoAsignacionService
{
    /**
     * Valida que se pueda asignar el conductor al vehículo.
     *
     * @throws ValidationException Si la licencia está vencida o la RT del vehículo está caducada.
     */
    public function validarPuedeAsignar(Conductor $conductor, Vehiculo $vehiculo): void
    {
        $errores = [];

        if ($conductor->licenciaVencida()) {
            $errores['conductor'] = [__('asignacion.bloqueo_licencia_vencida')];
        }

        if (! $vehiculo->revisionTecnicaVigente()) {
            $errores['vehiculo'] = [__('asignacion.bloqueo_rt_vencida')];
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }
    }

    /**
     * Indica si la asignación está permitida, sin lanzar excepción.
     */
    public function puedeAsignar(Conductor $conductor, Vehiculo $vehiculo): bool
    {
        if ($conductor->licenciaVencida()) {
            return false;
        }

        if (! $vehiculo->revisionTecnicaVigente()) {
            return false;
        }

        return true;
    }

    /**
     * Devuelve los motivos de bloqueo (para mostrarlos sin lanzar).
     *
     * @return array<string>
     */
    public function motivosBloqueo(Conductor $conductor, Vehiculo $vehiculo): array
    {
        $motivos = [];

        if ($conductor->licenciaVencida()) {
            $motivos[] = __('asignacion.bloqueo_licencia_vencida');
        }

        if (! $vehiculo->revisionTecnicaVigente()) {
            $motivos[] = __('asignacion.bloqueo_rt_vencida');
        }

        return $motivos;
    }
}
