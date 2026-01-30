<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(VehicleCategorySeeder::class);

        // Usuarios (varios roles). Contraseña: password
        $defaultPassword = Hash::make('password');
        User::firstOrCreate(
            ['email' => 'admin@melichinkul.cl'],
            ['name' => 'Admin Principal', 'email' => 'admin@melichinkul.cl', 'password' => $defaultPassword, 'role' => 'administrator', 'full_name' => 'Admin Principal']
        );
        User::firstOrCreate(
            ['email' => 'supervisor@melichinkul.cl'],
            ['name' => 'Ana Supervisor', 'email' => 'supervisor@melichinkul.cl', 'password' => $defaultPassword, 'role' => 'supervisor', 'full_name' => 'Ana Supervisor']
        );
        User::firstOrCreate(
            ['email' => 'adminis@melichinkul.cl'],
            ['name' => 'Pedro Administrativo', 'email' => 'adminis@melichinkul.cl', 'password' => $defaultPassword, 'role' => 'administrativo', 'full_name' => 'Pedro Administrativo']
        );
        User::firstOrCreate(
            ['email' => 'juan.perez@melichinkul.cl'],
            ['name' => 'Juan Pérez', 'email' => 'juan.perez@melichinkul.cl', 'password' => $defaultPassword, 'role' => 'technician', 'full_name' => 'Juan Pérez']
        );
        User::firstOrCreate(
            ['email' => 'maria.gonzalez@melichinkul.cl'],
            ['name' => 'María González', 'email' => 'maria.gonzalez@melichinkul.cl', 'password' => $defaultPassword, 'role' => 'technician', 'full_name' => 'María González']
        );
        User::firstOrCreate(
            ['email' => 'carlos.mecanico@melichinkul.cl'],
            ['name' => 'Carlos Mecánico', 'email' => 'carlos.mecanico@melichinkul.cl', 'password' => $defaultPassword, 'role' => 'technician', 'full_name' => 'Carlos Mecánico']
        );
        User::firstOrCreate(
            ['email' => 'visor@melichinkul.cl'],
            ['name' => 'Laura Visor', 'email' => 'visor@melichinkul.cl', 'password' => $defaultPassword, 'role' => 'viewer', 'full_name' => 'Laura Visor']
        );

        $this->call(VehicleSeeder::class);
        $this->call(DriverSeeder::class);
        $this->call(CertificationSeeder::class);
        $this->call(DriverAssignmentSeeder::class);
        $this->call(MaintenanceSeeder::class);

        Artisan::call('alerts:generate');
    }
}
