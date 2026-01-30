<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            $table->string('type', 32); // purchase, adjustment_in, adjustment_out, use, return
            $table->integer('quantity'); // positive = in, negative = out
            $table->string('reference_type', 128)->nullable(); // e.g. App\Models\PurchaseItem
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->date('movement_date');
            $table->timestamps();

            $table->index(['spare_part_id', 'movement_date']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
