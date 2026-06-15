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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->enum('type', ['offline', 'online', 'hybrid']);
            $table->string('category', 100)->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'archived'])->default('draft');
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('timezone', 50);
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->string('venue_city', 100)->nullable();
            $table->string('venue_map_url', 500)->nullable();
            $table->string('online_url', 500)->nullable();
            $table->integer('capacity')->nullable();
            $table->dateTime('registration_deadline')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('refund_policy')->nullable();
            $table->text('terms')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('thank_you_message')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
