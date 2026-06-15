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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('attendee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('channel', ['email', 'whatsapp', 'sms']);
            $table->string('type', 100);
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->enum('status', ['queued', 'sent', 'failed']);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
