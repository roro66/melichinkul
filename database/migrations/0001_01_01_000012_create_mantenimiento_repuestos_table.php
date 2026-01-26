<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mantenimiento_repuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mantenimiento_id')->constrained('mantenimientos')->cascadeOnDelete();
            $table->foreignId('repuesto_id')->nullable()->constrained('repuestos')->nullOnDelete();
            $table->string('codigo_repuesto')->nullable();
            $table->string('descripcion');
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->unsignedBigInteger('precio_unitario');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });

        Schema::table('mantenimiento_repuestos', function (Blueprint $table) {
            $table->index('mantenimiento_id');
            $table->index('repuesto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimiento_repuestos');
    }
};
