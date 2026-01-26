<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->string('contador_principal', 32)->default('kilometraje');
            $table->string('criticidad_default', 32)->default('media');
            $table->boolean('requiere_certificaciones')->default(true);
            $table->boolean('requiere_certificaciones_especiales')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_vehiculos');
    }
};
