<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_checklist_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->constrained('maintenances')->cascadeOnDelete();
            $table->foreignId('maintenance_checklist_item_id')->constrained('maintenance_checklist_items')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->foreignId('completed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['maintenance_id', 'maintenance_checklist_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_checklist_completions');
    }
};
