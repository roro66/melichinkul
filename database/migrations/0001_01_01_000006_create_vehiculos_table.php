<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('patente')->unique();
            $table->string('marca');
            $table->string('modelo');
            $table->unsignedSmallInteger('anio');
            $table->string('numero_motor')->nullable();
            $table->string('numero_chasis')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_vehiculos')->nullOnDelete();
            $table->string('tipo_combustible', 32)->default('gasolina');
            $table->string('estado', 32)->default('activo');
            $table->decimal('kilometraje_actual', 12, 2)->default(0);
            $table->decimal('horometro_actual', 12, 2)->default(0);
            $table->foreignId('conductor_actual_id')->nullable()->constrained('conductores')->nullOnDelete();
            $table->date('fecha_incorporacion');
            $table->unsignedBigInteger('valor_compra')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('vehiculos', function (Blueprint $table) {
            $table->index('categoria_id');
            $table->index('estado');
            $table->index('conductor_actual_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
