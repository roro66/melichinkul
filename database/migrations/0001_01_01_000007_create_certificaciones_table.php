<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->cascadeOnDelete();
            $table->string('tipo', 64);
            $table->string('nombre');
            $table->string('numero_certificado')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento');
            $table->string('proveedor')->nullable();
            $table->unsignedBigInteger('costo')->nullable();
            $table->string('archivo_adjunto')->nullable();
            $table->string('archivo_adjunto_2')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('obligatorio')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('certificaciones', function (Blueprint $table) {
            $table->index('vehiculo_id');
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificaciones');
    }
};
