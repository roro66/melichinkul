<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Validation\ValidationException;

/**
 * Plan 13.4: Impide asignar vehículo a conductor si:
 * 1. La licencia de conducir del conductor está vencida.
 * 2. El vehículo tiene la Revisión Técnica caducada.
 *
 * La asignación no debe persistirse; se muestra un mensaje claro al usuario.
 */
class BlockAssignmentService
{
    /**
     * Valida que se pueda asignar el conductor al vehículo.
     *
     * @throws ValidationException Si la licencia está vencida o la RT del vehículo está caducada.
     */
    public function validateCanAssign(Driver $driver, Vehicle $vehicle): void
    {
        $errors = [];

        if ($driver->hasExpiredLicense()) {
            $errors['driver'] = [__('asignacion.bloqueo_licencia_vencida')];
        }

        if (! $vehicle->hasValidTechnicalReview()) {
            $errors['vehicle'] = [__('asignacion.bloqueo_rt_vencida')];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Indica si la asignación está permitida, sin lanzar excepción.
     */
    public function canAssign(Driver $driver, Vehicle $vehicle): bool
    {
        if ($driver->hasExpiredLicense()) {
            return false;
        }

        if (! $vehicle->hasValidTechnicalReview()) {
            return false;
        }

        return true;
    }

    /**
     * Devuelve los motivos de bloqueo (para mostrarlos sin lanzar).
     *
     * @return array<string>
     */
    public function blockReasons(Driver $driver, Vehicle $vehicle): array
    {
        $reasons = [];

        if ($driver->hasExpiredLicense()) {
            $reasons[] = __('asignacion.bloqueo_licencia_vencida');
        }

        if (! $vehicle->hasValidTechnicalReview()) {
            $reasons[] = __('asignacion.bloqueo_rt_vencida');
        }

        return $reasons;
    }
}
