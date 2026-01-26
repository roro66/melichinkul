<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_conductores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conductor_id')->constrained('conductores')->cascadeOnDelete();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnDelete();
            $table->date('fecha_asignacion');
            $table->date('fecha_fin')->nullable();
            $table->foreignId('asignado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo_fin', 32)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::table('asignaciones_conductores', function (Blueprint $table) {
            $table->index(['conductor_id', 'vehiculo_id']);
            $table->index(['vehiculo_id', 'fecha_asignacion']);
            $table->index('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_conductores');
    }
};
