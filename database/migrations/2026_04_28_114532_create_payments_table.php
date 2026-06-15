<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // 🔥 Offline support
            $table->uuid('local_uuid')->nullable()->unique();

            // 🔹 Student
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // 🔹 Enrollment (category-level enrollment)
            $table->foreignId('student_class_enrollment_id')
                ->constrained('student_class_enrollments')
                ->cascadeOnDelete();

            // 🔹 Collected by
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('mark_method', [
                'qr_mobile',
                'qr_web',
                'manual_mobile',
                'manual_web'
            ])->default('qr_mobile');

            // 💰 Paid amount only
            $table->decimal('amount', 10, 2);
            

            // Optional discount tracking
            $table->decimal('discount_amount', 10, 2)->default(0);

            // Payment time
            $table->dateTime('paid_at');

            // 🔥 Monthly control (IMPORTANT)
            $table->date('payment_month');

            // Payment method
            $table->enum('payment_method', [
                'cash',
                'card',
                'bank_transfer',
                'online',
                'cheque',
                'other'
            ])->default('cash');

            // Status
            $table->enum('status', [
                'completed',
                'pending',
                'failed',
                'cancelled'
            ])->default('completed');

            // Receipt
            $table->string('receipt_number', 100)->nullable()->unique();
            $table->string('reference_number', 100)->nullable();

            // Sync
            $table->boolean('is_synced')->default(true);

            // Notes
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // 🔥 IMPORTANT BUSINESS RULE
            // same enrollment + same month = only one payment
            $table->unique([
                'student_class_enrollment_id',
                'payment_month',
            ], 'unique_enrollment_month_payment');

            // indexes
            $table->index(['student_id', 'paid_at']);
            $table->index(['student_class_enrollment_id', 'paid_at']);
            $table->index(['status', 'paid_at']);
            $table->index(['payment_month', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
