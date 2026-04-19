<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La columna responsible_technician_id es NOT NULL; la FK no puede usar ON DELETE SET NULL.
     */
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['responsible_technician_id']);
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreign('responsible_technician_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropForeign(['responsible_technician_id']);
        });

        Schema::table('maintenances', function (Blueprint $table) {
            $table->foreign('responsible_technician_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};
