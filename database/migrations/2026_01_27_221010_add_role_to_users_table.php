<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->string("role", 32)->default("viewer")->after("email");
            $table->string("full_name")->nullable()->after("name");
            $table->boolean("email_notifications")->default(true)->after("role");
            $table->string("phone")->nullable()->after("email_notifications");
            $table->boolean("active")->default(true)->after("phone");
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn(["role", "full_name", "email_notifications", "phone", "active"]);
        });
    }
};
