<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->string('name'); // Nombre legible: Carnet, Licencia, Certificado de antecedentes, etc.
            $table->string('file_path'); // Ruta en storage
            $table->string('original_name')->nullable(); // Nombre original del archivo
            $table->timestamps();

            $table->index('driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_documents');
    }
};
