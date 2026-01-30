<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Support\ChileanValidationHelper;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Luis Martínez', 'Carlos Rodríguez', 'Pedro Sánchez', 'José López', 'Francisco Torres',
            'Andrés Flores', 'Ricardo Díaz', 'Jorge Ramírez', 'Fernando González', 'Roberto Silva',
            'Miguel Hernández', 'Daniel Castro', 'Alejandro Vargas', 'Sergio Rojas', 'Pablo Muñoz',
            'Eduardo Contreras', 'Raúl Soto', 'Héctor Morales', 'Claudio Reyes', 'Patricio Jiménez',
            'Mauricio Espinoza', 'Cristian Núñez', 'Gonzalo Bravo', 'Felipe Campos', 'Sebastián Vega',
            'Nicolás Sandoval', 'Bruno Figueroa', 'Álvaro Guzmán', 'Tomás Valenzuela', 'Maximiliano Salazar',
        ];

        $usedRuts = [];
        $today = Carbon::today();

        foreach ($names as $index => $fullName) {
            $rut = $this->generateUniqueRut($usedRuts);
            $usedRuts[] = $rut;

            // Licencia: 1/3 vencida, 1/3 por vencer (< 60 días), 1/3 vigente
            $licenseScenario = $index % 3;
            if ($licenseScenario === 0) {
                $licenseExpiration = $today->copy()->subDays(rand(5, 90));
                $licenseIssue = $licenseExpiration->copy()->subYears(5);
            } elseif ($licenseScenario === 1) {
                $licenseExpiration = $today->copy()->addDays(rand(10, 55));
                $licenseIssue = $licenseExpiration->copy()->subYears(5);
            } else {
                $licenseExpiration = $today->copy()->addMonths(rand(4, 18));
                $licenseIssue = $licenseExpiration->copy()->subYears(5);
            }

            Driver::updateOrCreate(
                ['rut' => $rut],
                [
                    'rut' => $rut,
                    'full_name' => $fullName,
                    'phone' => '+56 9 ' . rand(5000, 9999) . ' ' . rand(1000, 9999),
                    'email' => strtolower(str_replace(' ', '.', $fullName)) . '@melichinkul.cl',
                    'license_number' => 'CH-' . rand(100000, 999999),
                    'license_class' => ['B', 'C', 'D', 'E'][array_rand(['B', 'C', 'D', 'E'])],
                    'license_issue_date' => $licenseIssue,
                    'license_expiration_date' => $licenseExpiration,
                    'active' => rand(0, 10) > 1,
                    'observations' => rand(0, 3) === 0 ? 'Conductor de reemplazo en temporada alta.' : null,
                ]
            );
        }
    }

    private function generateUniqueRut(array $usedRuts): string
    {
        do {
            $body = (string) rand(5000000, 24000000);
            $dv = ChileanValidationHelper::calcularDigitoVerificador($body);
            $rut = $body . '-' . $dv;
        } while (in_array($rut, $usedRuts, true));

        return $rut;
    }
}
