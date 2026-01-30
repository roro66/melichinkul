<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->foreignId('spare_part_id')->nullable()->after('vehicle_id')->constrained('spare_parts')->nullOnDelete();
        });
        DB::statement('ALTER TABLE alerts ALTER COLUMN vehicle_id DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DELETE FROM alerts WHERE vehicle_id IS NULL');
        DB::statement('ALTER TABLE alerts ALTER COLUMN vehicle_id SET NOT NULL');
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropForeign(['spare_part_id']);
        });
    }
};
