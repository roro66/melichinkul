<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("certifications", function (Blueprint $table) {
            $table->id();
            $table->foreignId("vehicle_id")->constrained("vehicles")->cascadeOnDelete();
            $table->string("type", 64);
            $table->string("name");
            $table->string("certificate_number")->nullable();
            $table->date("issue_date")->nullable();
            $table->date("expiration_date");
            $table->string("provider")->nullable();
            $table->unsignedBigInteger("cost")->nullable();
            $table->string("attached_file")->nullable();
            $table->string("attached_file_2")->nullable();
            $table->text("observations")->nullable();
            $table->boolean("required")->default(true);
            $table->boolean("active")->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index("vehicle_id");
            $table->index("expiration_date");
            $table->index("type");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("certifications");
    }
};
