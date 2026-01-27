<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("vehicles", function (Blueprint $table) {
            $table->id();
            $table->string("license_plate")->unique();
            $table->string("brand");
            $table->string("model");
            $table->unsignedSmallInteger("year");
            $table->string("engine_number")->nullable();
            $table->string("chassis_number")->nullable();
            $table->foreignId("category_id")->nullable()->constrained("vehicle_categories")->nullOnDelete();
            $table->string("fuel_type", 32)->default("gasoline");
            $table->string("status", 32)->default("active");
            $table->decimal("current_mileage", 12, 2)->default(0);
            $table->decimal("current_hours", 12, 2)->default(0);
            $table->foreignId("current_driver_id")->nullable()->constrained("drivers")->nullOnDelete();
            $table->date("incorporation_date");
            $table->unsignedBigInteger("purchase_value")->nullable();
            $table->text("observations")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index("license_plate");
            $table->index("category_id");
            $table->index("status");
            $table->index("current_driver_id");
            $table->index("engine_number");
            $table->index("chassis_number");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("vehicles");
    }
};
