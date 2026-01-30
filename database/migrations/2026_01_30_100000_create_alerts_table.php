<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('maintenance_id')->nullable()->constrained('maintenances')->nullOnDelete();
            $table->string('type', 64);
            $table->string('severity', 32)->default('informativa');
            $table->string('title');
            $table->text('message')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->date('due_date')->nullable();
            $table->string('status', 32)->default('pending');
            $table->foreignId('closed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->foreignId('snoozed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('snoozed_reason')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'status']);
            $table->index(['severity', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
