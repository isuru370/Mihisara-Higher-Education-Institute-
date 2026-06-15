<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtraIncomesTable extends Migration
{
    public function up()
    {
        Schema::create('extra_incomes', function (Blueprint $table) {
            $table->id();

            // 💰 amount
            $table->decimal('amount', 12, 2);

            // income date
            $table->date('income_date');

            // reason
            $table->string('reason', 150)->nullable();

            // 🔥 income type (future ready)
            $table->enum('income_type', [
                'hall_rent',
                'extra',
                'refund',
                'other'
            ])->default('hall_rent');

            // status
            $table->enum('status', [
                'pending',
                'approved',
                'received',
                'cancelled'
            ])->default('received');

            // user
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // notes
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index(['income_date', 'status']);
            $table->index('income_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extra_incomes');
    }
}