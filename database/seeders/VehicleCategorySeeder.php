<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Utilitarios Ligeros',
                'slug' => 'utilitarios',
                'description' => 'Vehículos utilitarios ligeros (ej. Fiat Berlingo, Peugeot Partner)',
                'main_counter' => 'mileage',
                'default_criticality' => 'medium',
                'requires_certifications' => true,
                'requires_special_certifications' => false,
                'active' => true,
            ],
            [
                'name' => 'Camionetas',
                'slug' => 'camionetas',
                'description' => 'Camionetas medianas y grandes (ej. Nissan Navara, Ford F150)',
                'main_counter' => 'mileage',
                'default_criticality' => 'medium',
                'requires_certifications' => true,
                'requires_special_certifications' => false,
                'active' => true,
            ],
            [
                'name' => 'Camiones Grúa',
                'slug' => 'camiones-grua',
                'description' => 'Camiones con grúa pluma para movimiento de estanques',
                'main_counter' => 'mixed',
                'default_criticality' => 'high',
                'requires_certifications' => true,
                'requires_special_certifications' => true,
                'active' => true,
            ],
            [
                'name' => 'Maquinaria Especial',
                'slug' => 'maquinaria',
                'description' => 'Maquinaria especial (ej. Bobcat). Uso por horómetro.',
                'main_counter' => 'hours',
                'default_criticality' => 'high',
                'requires_certifications' => true,
                'requires_special_certifications' => true,
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            VehicleCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
