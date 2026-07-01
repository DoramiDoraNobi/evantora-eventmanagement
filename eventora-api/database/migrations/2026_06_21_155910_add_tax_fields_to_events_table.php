<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_taxable')->default(false)->after('status');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('is_taxable');
            $table->string('tax_name', 50)->default('Tax')->after('tax_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_taxable', 'tax_rate', 'tax_name']);
        });
    }
};
