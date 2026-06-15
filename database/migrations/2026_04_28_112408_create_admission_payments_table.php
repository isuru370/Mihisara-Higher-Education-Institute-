<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmissionPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('admission_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('admission_id')
                ->constrained('admissions')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('amount', 10, 2);

            $table->dateTime('paid_at')->nullable();

            $table->enum('payment_method', [
                'cash',
                'card',
                'bank_transfer',
                'online',
                'other',
            ])->default('cash');

            $table->enum('status', [
                'pending',
                'paid',
                'cancelled',
                'refunded',
            ])->default('paid');

            $table->string('receipt_number', 100)->nullable()->unique();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'status']);
            $table->index(['admission_id', 'status']);
            $table->index(['paid_at', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('admission_payments');
    }
}