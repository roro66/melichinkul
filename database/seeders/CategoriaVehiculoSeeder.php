<?php

namespace Database\Seeders;

use App\Models\CategoriaVehiculo;
use Illuminate\Database\Seeder;

class CategoriaVehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Utilitarios Ligeros',
                'slug' => 'utilitarios',
                'descripcion' => 'Vehículos utilitarios ligeros (ej. Fiat Berlingo, Peugeot Partner)',
                'contador_principal' => 'kilometraje',
                'criticidad_default' => 'media',
                'requiere_certificaciones' => true,
                'requiere_certificaciones_especiales' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Camionetas',
                'slug' => 'camionetas',
                'descripcion' => 'Camionetas medianas y grandes (ej. Nissan Navara, Ford F150)',
                'contador_principal' => 'kilometraje',
                'criticidad_default' => 'media',
                'requiere_certificaciones' => true,
                'requiere_certificaciones_especiales' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Camiones Grúa',
                'slug' => 'camiones-grua',
                'descripcion' => 'Camiones con grúa pluma para movimiento de estanques',
                'contador_principal' => 'mixto',
                'criticidad_default' => 'alta',
                'requiere_certificaciones' => true,
                'requiere_certificaciones_especiales' => true,
                'activo' => true,
            ],
            [
                'nombre' => 'Maquinaria Especial',
                'slug' => 'maquinaria',
                'descripcion' => 'Maquinaria especial (ej. Bobcat). Uso por horómetro.',
                'contador_principal' => 'horometro',
                'criticidad_default' => 'alta',
                'requiere_certificaciones' => true,
                'requiere_certificaciones_especiales' => true,
                'activo' => true,
            ],
        ];

        foreach ($categorias as $cat) {
            CategoriaVehiculo::firstOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }
    }
}
