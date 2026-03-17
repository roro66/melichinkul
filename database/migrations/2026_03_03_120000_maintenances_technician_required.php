<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $firstTechnician = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'technician')
            ->where('model_type', 'App\\Models\\User')
            ->orderBy('model_id')
            ->value('model_id');

        if (! $firstTechnician) {
            $firstTechnician = DB::table('users')->orderBy('id')->value('id');
        }

        if (! $firstTechnician) {
            return;
        }

        DB::table('maintenances')
            ->whereNull('responsible_technician_id')
            ->update(['responsible_technician_id' => $firstTechnician]);

        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE maintenances ALTER COLUMN responsible_technician_id SET NOT NULL');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE maintenances MODIFY responsible_technician_id BIGINT UNSIGNED NOT NULL');
        }
        // SQLite: no soporta SET NOT NULL fácil; en tests dejar nullable implícito si aplica
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE maintenances ALTER COLUMN responsible_technician_id DROP NOT NULL');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE maintenances MODIFY responsible_technician_id BIGINT UNSIGNED NULL');
        }
    }
};
