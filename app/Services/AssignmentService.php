<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrates driver–vehicle assignment:
 * - Validates blocks (license, technical review) via BlockAssignmentService.
 * - Closes the vehicle's previous assignment.
 * - Creates the new assignment and updates vehicle.current_driver_id.
 */
class AssignmentService
{
    public function __construct(
        protected BlockAssignmentService $blockService
    ) {}

    /**
     * Assigns a driver to a vehicle.
     * Validates blocks, closes previous assignment, creates new one and updates vehicle.
     *
     * @param  \Carbon\CarbonInterface|string|null  $assignmentDate  Default today.
     * @throws ValidationException If license expired or vehicle's technical review expired.
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
