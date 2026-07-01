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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add category_id as nullable FK and drop the old string category column
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->default(null)->after('type');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('type');
        });

        Schema::dropIfExists('categories');
    }
};
