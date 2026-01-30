<?php

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CertificationSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = Vehicle::all();
        if ($vehicles->isEmpty()) {
            $this->command->warn('No hay vehículos. Ejecuta primero VehicleSeeder.');
            return;
        }

        $today = Carbon::today();
        $certTypes = [
            ['type' => 'technical_review', 'name' => 'Revisión Técnica', 'required' => true],
            ['type' => 'soap', 'name' => 'SOAP', 'required' => true],
            ['type' => 'permiso_circulacion', 'name' => 'Permiso de Circulación', 'required' => true],
            ['type' => 'analisis_gases', 'name' => 'Análisis de Gases', 'required' => true],
        ];

        foreach ($vehicles as $vehicle) {
            foreach ($certTypes as $cert) {
                // Variar vencimientos: algunas vencidas, algunas en 15 días, 60 días, 1 año
                $scenario = rand(0, 3);
                if ($scenario === 0) {
                    $expiration = $today->copy()->subDays(rand(5, 120));
                } elseif ($scenario === 1) {
                    $expiration = $today->copy()->addDays(rand(5, 20));
                } elseif ($scenario === 2) {
                    $expiration = $today->copy()->addDays(rand(30, 55));
                } else {
                    $expiration = $today->copy()->addMonths(rand(4, 14));
                }
                $issue = $expiration->copy()->subYear();

                Certification::updateOrCreate(
                    [
                        'vehicle_id' => $vehicle->id,
                        'type' => $cert['type'],
                    ],
                    [
                        'vehicle_id' => $vehicle->id,
                        'type' => $cert['type'],
                        'name' => $cert['name'] . ' ' . $expiration->format('Y'),
                        'certificate_number' => $cert['type'] === 'soap' ? 'POL-' . rand(100000, 999999) : null,
                        'issue_date' => $issue,
                        'expiration_date' => $expiration,
                        'provider' => $cert['type'] === 'soap' ? 'Compañía de Seguros' : ($cert['type'] === 'technical_review' ? 'Centro de Revisión Técnica' : 'Municipalidad'),
                        'cost' => rand(15000, 85000),
                        'required' => $cert['required'],
                        'active' => true,
                    ]
                );
            }
        }
    }
}
