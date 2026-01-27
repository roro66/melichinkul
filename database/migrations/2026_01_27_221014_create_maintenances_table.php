<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("maintenances", function (Blueprint $table) {
            $table->id();
            $table->foreignId("vehicle_id")->constrained("vehicles")->cascadeOnDelete();
            $table->string("type", 32);
            $table->string("status", 32)->default("scheduled");
            $table->date("scheduled_date");
            $table->date("start_date")->nullable();
            $table->date("end_date")->nullable();
            $table->decimal("mileage_at_maintenance", 12, 2)->nullable();
            $table->decimal("hours_at_maintenance", 12, 2)->nullable();
            $table->text("entry_reason")->nullable();
            $table->text("work_description");
            $table->text("work_performed")->nullable();
            $table->unsignedBigInteger("parts_cost")->default(0);
            $table->unsignedBigInteger("labor_cost")->default(0);
            $table->unsignedBigInteger("total_cost")->default(0);
            $table->decimal("hours_worked", 6, 2)->nullable();
            $table->string("workshop_supplier")->nullable();
            $table->foreignId("responsible_technician_id")->nullable()->constrained("users")->nullOnDelete();
            $table->foreignId("assigned_driver_id")->nullable()->constrained("drivers")->nullOnDelete();
            $table->text("observations")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(["vehicle_id", "status"]);
            $table->index("scheduled_date");
            $table->index("end_date");
            $table->index("assigned_driver_id");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("maintenances");
    }
};
