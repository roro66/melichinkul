<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BlockAssignmentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DriverAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::all();
        $drivers = Driver::where('active', true)->get();
        $users = User::all();

        if ($vehicles->isEmpty() || $drivers->isEmpty()) {
            $this->command->warn('Se requieren vehículos y conductores activos.');
            return;
        }

        $blockService = app(BlockAssignmentService::class);
        $assigned = 0;

        $toAssign = $vehicles->random(min(12, $vehicles->count()));
        foreach ($toAssign as $vehicle) {
            $driver = $drivers->random();
            if (! $blockService->canAssign($driver, $vehicle)) {
                continue;
            }
            $date = Carbon::today()->subDays(rand(5, 90));
            DriverAssignment::firstOrCreate(
                [
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driver->id,
                    'assignment_date' => $date,
                    'end_date' => null,
                ],
                [
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driver->id,
                    'assignment_date' => $date,
                    'end_date' => null,
                    'assigned_by_id' => $users->isNotEmpty() ? $users->random()->id : null,
                    'observations' => null,
                ]
            );
            $vehicle->update(['current_driver_id' => $driver->id]);
            $assigned++;
        }
    }
}
