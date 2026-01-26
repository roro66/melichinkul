<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repuestos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('descripcion');
            $table->string('marca')->nullable();
            $table->string('categoria', 32)->default('repuesto');
            $table->unsignedInteger('stock_actual')->default(0);
            $table->unsignedInteger('stock_minimo_manual')->default(0);
            $table->unsignedBigInteger('precio_referencia')->nullable();
            $table->boolean('tiene_vencimiento')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('repuestos', function (Blueprint $table) {
            $table->index('codigo');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repuestos');
    }
};
