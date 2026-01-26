<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnDelete();
            $table->string('tipo', 32); // preventivo, correctivo, inspeccion
            $table->string('estado', 32)->default('programado');
            $table->date('fecha_programada');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('kilometraje_en_mantenimiento', 12, 2)->nullable();
            $table->decimal('horometro_en_mantenimiento', 12, 2)->nullable();
            $table->text('motivo_ingreso')->nullable();
            $table->text('descripcion_trabajo');
            $table->text('trabajos_realizados')->nullable();
            $table->unsignedBigInteger('costo_repuestos')->default(0);
            $table->unsignedBigInteger('costo_mano_obra')->default(0);
            $table->unsignedBigInteger('costo_total')->default(0);
            $table->decimal('horas_trabajadas', 6, 2)->nullable();
            $table->string('taller_proveedor')->nullable();
            $table->foreignId('tecnico_responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('conductor_asignado_id')->nullable()->constrained('conductores')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->index(['vehiculo_id', 'estado']);
            $table->index('fecha_programada');
            $table->index('fecha_fin');
            $table->index('conductor_asignado_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
