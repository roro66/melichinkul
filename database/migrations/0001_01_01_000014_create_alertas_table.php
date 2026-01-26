<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos')->cascadeOnDelete();
            $table->foreignId('conductor_id')->nullable()->constrained('conductores')->cascadeOnDelete();
            $table->foreignId('certificacion_id')->nullable()->constrained('certificaciones')->nullOnDelete();
            $table->foreignId('mantenimiento_id')->nullable()->constrained('mantenimientos')->nullOnDelete();
            $table->foreignId('repuesto_id')->nullable()->constrained('repuestos')->nullOnDelete();
            $table->string('tipo', 64);
            $table->string('severidad', 32)->default('informativa');
            $table->string('titulo');
            $table->text('mensaje');
            $table->timestamp('fecha_generada');
            $table->date('fecha_limite')->nullable();
            $table->string('estado', 32)->default('pendiente');
            $table->foreignId('cerrada_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_cierre')->nullable();
            $table->text('motivo_cierre')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->foreignId('snoozed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('snoozed_reason')->nullable();
            $table->timestamps();
        });

        Schema::table('alertas', function (Blueprint $table) {
            $table->index(['vehiculo_id', 'estado']);
            $table->index(['severidad', 'estado']);
            $table->index('fecha_limite');
            $table->index('fecha_generada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
