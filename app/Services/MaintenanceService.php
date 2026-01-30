<?php

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Validation\ValidationException;

class MaintenanceService
{
    /**
     * Validates that the maintenance can be closed (completed status).
     * Corrective maintenance cannot be closed without invoice and/or photo evidence.
     *
     * @throws ValidationException If corrective and missing required evidence.
     */
    public function validateClosing(Maintenance $maintenance): void
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
     * Returns whether the maintenance can be closed, without throwing.
     */
    public function canClose(Maintenance $maintenance): bool
    {
        if (! $maintenance->isCorrective()) {
            return true;
        }

        return $maintenance->hasRequiredEvidence();
    }

    /**
     * Closes the maintenance (completed status) validating evidence for corrective type.
     *
     * @param  array<string, mixed>  $data  Fields to update (end_date, work_performed, costs, etc.)
     * @throws ValidationException If corrective without required evidence.
     */
    public function close(Maintenance $maintenance, array $data): void
    {
        $this->validateClosing($maintenance);

        $maintenance->update(array_merge($data, ['status' => 'completed']));
    }
}
