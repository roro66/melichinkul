<?php

namespace Database\Seeders;

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MantenimientoSeeder extends Seeder
{
    public function run(): void
    {
        $vehiculos = Vehiculo::all();
        $tecnicos = User::whereIn("rol", ["tecnico", "administrador"])->get();
        
        if ($vehiculos->isEmpty()) {
            $this->command->warn("No hay vehículos en la base de datos. Ejecuta primero VehiculoSeeder.");
            return;
        }

        if ($tecnicos->isEmpty()) {
            $this->command->warn("No hay técnicos en la base de datos. Ejecuta primero DatabaseSeeder.");
            return;
        }

        $mantenimientos = [];

        foreach ($vehiculos as $vehiculo) {
            $mantenimientos[] = [
                "vehiculo_id" => $vehiculo->id,
                "tipo" => "preventivo",
                "estado" => "programado",
                "fecha_programada" => Carbon::now()->addDays(rand(5, 30)),
                "descripcion_trabajo" => "Mantenimiento preventivo: cambio de aceite, filtros y revisión general",
                "costo_repuestos" => rand(50000, 150000),
                "costo_mano_obra" => rand(80000, 120000),
                "costo_total" => 0,
                "tecnico_responsable_id" => $tecnicos->random()?->id,
            ];

            $mantenimientos[] = [
                "vehiculo_id" => $vehiculo->id,
                "tipo" => "preventivo",
                "estado" => "completado",
                "fecha_programada" => Carbon::now()->subDays(rand(15, 60)),
                "fecha_inicio" => Carbon::now()->subDays(rand(15, 60)),
                "fecha_fin" => Carbon::now()->subDays(rand(10, 55)),
                "kilometraje_en_mantenimiento" => $vehiculo->kilometraje_actual - rand(5000, 15000),
                "horometro_en_mantenimiento" => $vehiculo->horometro_actual > 0 ? $vehiculo->horometro_actual - rand(200, 500) : null,
                "descripcion_trabajo" => "Mantenimiento preventivo realizado: cambio de aceite, filtros de aire y combustible, revisión de frenos y suspensión",
                "trabajos_realizados" => "Cambio de aceite motor, filtro de aceite, filtro de aire, revisión de frenos, revisión de suspensión, alineación y balanceo",
                "costo_repuestos" => rand(60000, 180000),
                "costo_mano_obra" => rand(90000, 150000),
                "costo_total" => 0,
                "horas_trabajadas" => rand(2, 5),
                "taller_proveedor" => "Taller Interno",
                "tecnico_responsable_id" => $tecnicos->random()?->id,
            ];

            if (rand(0, 1)) {
                $mantenimientos[] = [
                    "vehiculo_id" => $vehiculo->id,
                    "tipo" => "correctivo",
                    "estado" => "completado",
                    "fecha_programada" => Carbon::now()->subDays(rand(30, 120)),
                    "fecha_inicio" => Carbon::now()->subDays(rand(30, 120)),
                    "fecha_fin" => Carbon::now()->subDays(rand(25, 115)),
                    "kilometraje_en_mantenimiento" => $vehiculo->kilometraje_actual - rand(10000, 30000),
                    "horometro_en_mantenimiento" => $vehiculo->horometro_actual > 0 ? $vehiculo->horometro_actual - rand(500, 1000) : null,
                    "motivo_ingreso" => $this->getMotivoIngreso(),
                    "descripcion_trabajo" => $this->getDescripcionCorrectivo(),
                    "trabajos_realizados" => $this->getTrabajosRealizados(),
                    "costo_repuestos" => rand(150000, 500000),
                    "costo_mano_obra" => rand(120000, 300000),
                    "costo_total" => 0,
                    "horas_trabajadas" => rand(4, 12),
                    "taller_proveedor" => rand(0, 1) ? "Taller Interno" : "Taller Externo - Servicio Rápido",
                    "tecnico_responsable_id" => $tecnicos->random()?->id,
                ];
            }

            if (rand(0, 2) === 0) {
                $mantenimientos[] = [
                    "vehiculo_id" => $vehiculo->id,
                    "tipo" => rand(0, 1) ? "preventivo" : "correctivo",
                    "estado" => "en_proceso",
                    "fecha_programada" => Carbon::now()->subDays(rand(1, 5)),
                    "fecha_inicio" => Carbon::now()->subDays(rand(1, 5)),
                    "kilometraje_en_mantenimiento" => $vehiculo->kilometraje_actual,
                    "horometro_en_mantenimiento" => $vehiculo->horometro_actual,
                    "descripcion_trabajo" => "Mantenimiento en curso: " . ($vehiculo->tipo_combustible === "diesel" ? "revisión sistema de inyección" : "revisión sistema eléctrico"),
                    "costo_repuestos" => rand(80000, 200000),
                    "costo_mano_obra" => rand(60000, 150000),
                    "costo_total" => 0,
                    "horas_trabajadas" => rand(1, 3),
                    "taller_proveedor" => "Taller Interno",
                    "tecnico_responsable_id" => $tecnicos->random()?->id,
                ];
            }
        }

        foreach ($mantenimientos as $mantenimiento) {
            $mantenimiento["costo_total"] = $mantenimiento["costo_repuestos"] + $mantenimiento["costo_mano_obra"];
            Mantenimiento::create($mantenimiento);
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
