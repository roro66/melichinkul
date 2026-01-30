<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->unique()->constrained('spare_parts')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->unsignedInteger('min_stock')->nullable();
            $table->string('location', 128)->nullable();
            $table->timestamps();

            $table->index('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
