<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::all();
        $technicians = User::whereIn("role", ["technician", "administrator"])->get();
        
        if ($vehicles->isEmpty()) {
            $this->command->warn("No hay vehículos en la base de datos. Ejecuta primero VehicleSeeder.");
            return;
        }

        if ($technicians->isEmpty()) {
            $this->command->warn("No hay técnicos en la base de datos. Ejecuta primero DatabaseSeeder.");
            return;
        }

        $maintenances = [];

        foreach ($vehicles as $vehicle) {
            $maintenances[] = [
                "vehicle_id" => $vehicle->id,
                "type" => "preventive",
                "status" => "scheduled",
                "scheduled_date" => Carbon::now()->addDays(rand(5, 30)),
                "work_description" => "Mantenimiento preventivo: cambio de aceite, filtros y revisión general",
                "parts_cost" => rand(50000, 150000),
                "labor_cost" => rand(80000, 120000),
                "total_cost" => 0,
                "responsible_technician_id" => $technicians->random()?->id,
            ];

            $maintenances[] = [
                "vehicle_id" => $vehicle->id,
                "type" => "preventive",
                "status" => "completed",
                "scheduled_date" => Carbon::now()->subDays(rand(15, 60)),
                "start_date" => Carbon::now()->subDays(rand(15, 60)),
                "end_date" => Carbon::now()->subDays(rand(10, 55)),
                "mileage_at_maintenance" => $vehicle->current_mileage - rand(5000, 15000),
                "hours_at_maintenance" => $vehicle->current_hours > 0 ? $vehicle->current_hours - rand(200, 500) : null,
                "work_description" => "Mantenimiento preventivo realizado: cambio de aceite, filtros de aire y combustible, revisión de frenos y suspensión",
                "work_performed" => "Cambio de aceite motor, filtro de aceite, filtro de aire, revisión de frenos, revisión de suspensión, alineación y balanceo",
                "parts_cost" => rand(60000, 180000),
                "labor_cost" => rand(90000, 150000),
                "total_cost" => 0,
                "hours_worked" => rand(2, 5),
                "workshop_supplier" => "Taller Interno",
                "responsible_technician_id" => $technicians->random()?->id,
            ];

            if (rand(0, 1)) {
                $maintenances[] = [
                    "vehicle_id" => $vehicle->id,
                    "type" => "corrective",
                    "status" => "completed",
                    "scheduled_date" => Carbon::now()->subDays(rand(30, 120)),
                    "start_date" => Carbon::now()->subDays(rand(30, 120)),
                    "end_date" => Carbon::now()->subDays(rand(25, 115)),
                    "mileage_at_maintenance" => $vehicle->current_mileage - rand(10000, 30000),
                    "hours_at_maintenance" => $vehicle->current_hours > 0 ? $vehicle->current_hours - rand(500, 1000) : null,
                    "entry_reason" => $this->getMotivoIngreso(),
                    "work_description" => $this->getDescripcionCorrectivo(),
                    "work_performed" => $this->getTrabajosRealizados(),
                    "parts_cost" => rand(150000, 500000),
                    "labor_cost" => rand(120000, 300000),
                    "total_cost" => 0,
                    "hours_worked" => rand(4, 12),
                    "workshop_supplier" => rand(0, 1) ? "Taller Interno" : "Taller Externo - Servicio Rápido",
                    "responsible_technician_id" => $technicians->random()?->id,
                ];
            }

            if (rand(0, 2) === 0) {
                $maintenances[] = [
                    "vehicle_id" => $vehicle->id,
                    "type" => rand(0, 1) ? "preventive" : "corrective",
                    "status" => "in_progress",
                    "scheduled_date" => Carbon::now()->subDays(rand(1, 5)),
                    "start_date" => Carbon::now()->subDays(rand(1, 5)),
                    "mileage_at_maintenance" => $vehicle->current_mileage,
                    "hours_at_maintenance" => $vehicle->current_hours,
                    "work_description" => "Mantenimiento en curso: " . ($vehicle->fuel_type === "diesel" ? "revisión sistema de inyección" : "revisión sistema eléctrico"),
                    "parts_cost" => rand(80000, 200000),
                    "labor_cost" => rand(60000, 150000),
                    "total_cost" => 0,
                    "hours_worked" => rand(1, 3),
                    "workshop_supplier" => "Taller Interno",
                    "responsible_technician_id" => $technicians->random()?->id,
                ];
            }
        }

        foreach ($maintenances as $maintenance) {
            $maintenance["total_cost"] = $maintenance["parts_cost"] + $maintenance["labor_cost"];
            Maintenance::create($maintenance);
        }
    }

    private function getMotivoIngreso(): string
    {
        $motivos = [
            "Falla en sistema de frenos",
            "Ruido anormal en motor",
            "Pérdida de potencia",
            "Fuga de aceite",
            "Problema eléctrico",
            "Desgaste excesivo de neumáticos",
            "Falla en sistema de transmisión",
            "Sobrecalentamiento del motor",
        ];
        return $motivos[array_rand($motivos)];
    }

    private function getDescripcionCorrectivo(): string
    {
        $descripciones = [
            "Reparación de sistema de frenos: cambio de pastillas, discos y líquido de frenos",
            "Reparación de motor: cambio de correa de distribución y bomba de agua",
            "Reparación eléctrica: cambio de alternador y batería",
            "Reparación de transmisión: ajuste y cambio de aceite",
            "Reparación de suspensión: cambio de amortiguadores delanteros",
            "Reparación de sistema de refrigeración: cambio de radiador y mangueras",
        ];
        return $descripciones[array_rand($descripciones)];
    }

    private function getTrabajosRealizados(): string
    {
        $trabajos = [
            "Cambio de pastillas de freno, discos delanteros, líquido de frenos, revisión de sistema hidráulico",
            "Cambio de correa de distribución, bomba de agua, termostato, revisión de sistema de refrigeración",
            "Cambio de alternador, batería, revisión de sistema eléctrico completo",
            "Ajuste de transmisión, cambio de aceite de caja, revisión de embrague",
            "Cambio de amortiguadores delanteros, revisión de brazos y rótulas",
            "Cambio de radiador, mangueras, termostato, purga de sistema",
        ];
        return $trabajos[array_rand($trabajos)];
    }
}
