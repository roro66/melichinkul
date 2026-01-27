<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(VehicleCategorySeeder::class);

        // Crear usuarios
        User::factory()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@melichinkul.cl',
            'role' => 'administrator',
        ]);

        User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@melichinkul.cl',
            'role' => 'technician',
        ]);

        User::factory()->create([
            'name' => 'María González',
            'email' => 'maria.gonzalez@melichinkul.cl',
            'role' => 'technician',
        ]);

        // Crear vehículos
        $this->call(VehicleSeeder::class);

        // Crear mantenimientos
        $this->call(MaintenanceSeeder::class);
    }
}
