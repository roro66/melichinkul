<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("drivers", function (Blueprint $table) {
            $table->id();
            $table->string("rut")->unique();
            $table->string("full_name");
            $table->string("phone")->nullable();
            $table->string("email")->nullable();
            $table->string("license_number")->nullable();
            $table->string("license_class")->nullable();
            $table->date("license_issue_date")->nullable();
            $table->date("license_expiration_date")->nullable();
            $table->string("license_file")->nullable();
            $table->boolean("active")->default(true);
            $table->text("observations")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index("rut");
            $table->index("active");
            $table->index("license_expiration_date");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("drivers");
    }
};
