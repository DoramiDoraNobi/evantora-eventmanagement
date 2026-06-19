<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE checkin_logs MODIFY COLUMN method ENUM('qr_scan', 'manual', 'search', 'image_upload') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE checkin_logs MODIFY COLUMN method ENUM('qr_scan', 'manual', 'search') NOT NULL");
        }
    }
};
