<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_documents', function (Blueprint $table) {
            $table->string('reverse_file_path')->nullable()->after('original_name');
            $table->string('reverse_original_name')->nullable()->after('reverse_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('driver_documents', function (Blueprint $table) {
            $table->dropColumn(['reverse_file_path', 'reverse_original_name']);
        });
    }
};
