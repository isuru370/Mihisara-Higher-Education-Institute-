<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstitutePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('institute_payments', function (Blueprint $table) {
            $table->id();

            // 💰 amount
            $table->decimal('amount', 12, 2);

            // payment date
            $table->date('payment_date');

            // reason
            $table->string('reason', 150)->nullable();

            $table->string('reason_code', 50)->nullable();
            $table->foreign('reason_code')
                ->references('reason_code')
                ->on('payment_reasons')
                ->nullOnDelete();

            // 🔥 type (future ready)
            $table->enum('payment_type', [
                'expense',
            ])->default('expense');

            // status
            $table->enum('status', [
                'pending',
                'approved',
                'paid',
                'cancelled'
            ])->default('paid');

            // user
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // note
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index(['payment_date', 'status']);
            $table->index('reason_code');
            $table->index('payment_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('institute_payments');
    }
}