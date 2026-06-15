<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizerPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('organizer_payments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('organizer_id')
                ->constrained('organizers')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('payment_type', [
                'salary',
                'advance',
                'deduction',
                'other',
            ])->default('advance');

            $table->decimal('amount', 10, 2);

            $table->date('payment_date');

            $table->string('reason_code', 50)->nullable();

            $table->foreign('reason_code')
                ->references('reason_code')
                ->on('payment_reasons')
                ->nullOnDelete();

            $table->string('reason', 150)->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'paid',
                'cancelled',
            ])->default('paid');

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index(['organizer_id', 'payment_type']);
            $table->index(['payment_date', 'status']);
            $table->index('reason_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizer_payments');
    }
}
