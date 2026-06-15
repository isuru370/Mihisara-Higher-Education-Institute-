<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('teacher_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('payment_type', [
                'advance',
                'deduction',
                'other'
            ])->default('advance');

            $table->decimal('amount', 10, 2);

            $table->date('payment_date');

            $table->string('reason', 150)->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'paid',
                'cancelled'
            ])->default('paid');

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index(['teacher_id', 'payment_type']);
            $table->index(['payment_date', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher_payments');
    }
}