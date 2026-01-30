<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->date('assignment_date');
            $table->date('end_date')->nullable();
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('end_reason', 32)->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['driver_id', 'vehicle_id']);
            $table->index(['vehicle_id', 'assignment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_assignments');
    }
};
