<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repuesto_id')->constrained('repuestos')->cascadeOnDelete();
            $table->string('tipo', 32);
            $table->decimal('cantidad', 10, 2);
            $table->date('fecha');
            $table->unsignedBigInteger('compra_id')->nullable();
            $table->foreignId('mantenimiento_id')->nullable()->constrained('mantenimientos')->nullOnDelete();
            $table->string('referencia_manual')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->index('repuesto_id');
            $table->index('tipo');
            $table->index('fecha');
            $table->index('compra_id');
            $table->index('mantenimiento_id');
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
