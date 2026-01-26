<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conductores', function (Blueprint $table) {
            $table->id();
            $table->string('rut')->unique();
            $table->string('nombre_completo');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('licencia_numero')->nullable();
            $table->string('licencia_clase')->nullable();
            $table->date('licencia_fecha_emision')->nullable();
            $table->date('licencia_vencimiento')->nullable();
            $table->string('licencia_archivo')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('conductores', function (Blueprint $table) {
            $table->index('activo');
            $table->index('licencia_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conductores');
    }
};
