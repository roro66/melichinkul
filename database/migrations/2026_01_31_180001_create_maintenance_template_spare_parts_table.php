<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_template_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_template_id')->constrained('maintenance_templates')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['maintenance_template_id', 'spare_part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_template_spare_parts');
    }
};
