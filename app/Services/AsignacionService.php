<?php

namespace App\Services;

use App\Models\DriverAssignment;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Orquesta la asignación conductor–vehículo:
 * - Valida bloqueos (licencia, RT) vía BloqueoAsignacionService.
 * - Cierra la asignación anterior del vehículo.
 * - Crea la nueva asignación y actualiza vehicle.current_driver_id.
 */
class AssignmentService
{
    public function __construct(
        protected BlockAssignmentService $blockService
    ) {}

    /**
     * Asigna un conductor a un vehículo.
     * Valida bloqueos, cierra asignación previa, crea nueva y actualiza vehicle.
     *
     * @param  \Carbon\CarbonInterface|string|null  $assignmentDate  Por defecto hoy.
     * @throws ValidationException Si licencia vencida o RT del vehículo caducada.
     */
    public function assign(
        Driver $driver,
        Vehicle $vehicle,
        ?User $assignedBy = null,
        $assignmentDate = null,
        ?string $observations = null
    ): DriverAssignment {
        $this->blockService->validateCanAssign($driver, $vehicle);

        $date = $assignmentDate ? \Carbon\Carbon::parse($assignmentDate)->toDateString() : now()->toDateString();

        return DB::transaction(function () use ($driver, $vehicle, $assignedBy, $date, $observations) {
            $active = DriverAssignment::where('vehicle_id', $vehicle->id)
                ->whereNull('end_date')
                ->first();

            if ($active) {
                $active->update(['end_date' => $date, 'end_reason' => 'driver_change']);
            }

            $assignment = DriverAssignment::create([
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'assignment_date' => $date,
                'end_date' => null,
                'assigned_by_id' => $assignedBy?->id,
                'observations' => $observations,
            ]);

            $vehicle->update(['current_driver_id' => $driver->id]);

            return $assignment;
        });
    }
}
