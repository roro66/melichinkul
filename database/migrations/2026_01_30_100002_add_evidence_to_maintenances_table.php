<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('evidence_invoice_path')->nullable()->after('observations');
            $table->string('evidence_photo_path')->nullable()->after('evidence_invoice_path');
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn(['evidence_invoice_path', 'evidence_photo_path']);
        });
    }
};
