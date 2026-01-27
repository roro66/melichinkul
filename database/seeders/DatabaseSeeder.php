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
        $this->call(CategoriaVehiculoSeeder::class);

        // Crear usuarios
        User::factory()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@melichinkul.cl',
            'rol' => 'administrador',
        ]);

        User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@melichinkul.cl',
            'rol' => 'tecnico',
        ]);

        User::factory()->create([
            'name' => 'María González',
            'email' => 'maria.gonzalez@melichinkul.cl',
            'rol' => 'tecnico',
        ]);

        // Crear vehículos
        $this->call(VehiculoSeeder::class);

        // Crear mantenimientos
        $this->call(MantenimientoSeeder::class);
    }
}
