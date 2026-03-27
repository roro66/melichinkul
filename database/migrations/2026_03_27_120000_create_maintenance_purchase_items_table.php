<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->nullable()->constrained('spare_parts')->nullOnDelete();
            $table->string('product_name');
            $table->string('supplier_name');
            $table->string('document_number');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('line_total');
            $table->string('document_image_path')->nullable();
            $table->timestamps();

            $table->index(['maintenance_id']);
            $table->index(['spare_part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_purchase_items');
    }
};
