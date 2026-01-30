<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('description');
            $table->string('brand')->nullable();
            $table->string('category', 32)->default('spare_part'); // spare_part, consumable, tool, supply
            $table->unsignedBigInteger('reference_price')->nullable();
            $table->boolean('has_expiration')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('category');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
