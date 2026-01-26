<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->string('tipo', 64); // evidencia_factura, evidencia_foto, etc.
            $table->string('nombre')->nullable();
            $table->string('archivo_path');
            $table->string('nombre_archivo')->nullable();
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('tamano')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->boolean('obligatorio')->default(false);
            $table->text('observaciones')->nullable();
            $table->foreignId('subido_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->index(['documentable_type', 'documentable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
