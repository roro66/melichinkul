<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('rut_tramites', 32)->nullable()->after('chassis_number');
            $table->string('rut_propietario', 32)->nullable()->after('rut_tramites');
            $table->boolean('tarjeta_combustible')->default(false)->after('rut_propietario');
            $table->boolean('gps')->default(false)->after('tarjeta_combustible');
            $table->string('tire_size', 64)->nullable()->after('gps');
            $table->date('mileage_updated_at')->nullable()->after('current_mileage');
            // Elementos de seguridad (texto libre: "Sí", "Sí - Revisado el 20-04-23", etc.)
            $table->string('safety_gata', 128)->nullable()->after('observations');
            $table->string('safety_llave_rueda', 128)->nullable()->after('safety_gata');
            $table->string('safety_triangulo', 128)->nullable()->after('safety_llave_rueda');
            $table->string('safety_botiquin', 128)->nullable()->after('safety_triangulo');
            $table->string('safety_gancho_arrastre', 128)->nullable()->after('safety_botiquin');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'rut_tramites',
                'rut_propietario',
                'tarjeta_combustible',
                'gps',
                'tire_size',
                'mileage_updated_at',
                'safety_gata',
                'safety_llave_rueda',
                'safety_triangulo',
                'safety_botiquin',
                'safety_gancho_arrastre',
            ]);
        });
    }
};
