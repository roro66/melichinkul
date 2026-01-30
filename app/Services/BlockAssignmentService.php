<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Validation\ValidationException;

/**
 * Prevents assigning a vehicle to a driver when:
 * 1. The driver's license is expired.
 * 2. The vehicle's technical review (Revisión Técnica) is expired.
 *
 * Assignment must not be persisted; a clear message is shown to the user.
 */
class BlockAssignmentService
{
    /**
     * Validates that the driver can be assigned to the vehicle.
     *
     * @throws ValidationException If license is expired or vehicle's technical review is expired.
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
     * Returns whether assignment is allowed, without throwing.
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
     * Returns the block reasons (for display without throwing).
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
