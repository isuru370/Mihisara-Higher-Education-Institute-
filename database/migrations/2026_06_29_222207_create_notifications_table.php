<?php

use App\Enums\NotificationType;
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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Student who receives the notification
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            // Notification content
            $table->string('title', 150);
            $table->text('body');

            // Notification category
            $table->string('type', 50)->default(NotificationType::GENERAL);

            // Extra information (class_id, payment_id, etc.)
            $table->json('data')->nullable();

            // Notification state
            $table->enum('status', [
                'pending',
                'processing',
                'sent',
                'failed',
                'cancelled'
            ])->default('pending');

            // Tracking
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            // Firebase error (if any)
            $table->text('error_message')->nullable();

            // Retry counter
            $table->integer('retry_count')->default(0);

            // Admin/User who created notification
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('student_id');
            $table->index('status');
            $table->index('type');
            $table->index('read_at');
            $table->index('scheduled_at');
            $table->index(['student_id', 'read_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};