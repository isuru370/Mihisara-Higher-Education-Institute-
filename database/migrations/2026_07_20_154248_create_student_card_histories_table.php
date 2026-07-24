<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentCardHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_card_histories', function (Blueprint $table) {

            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('card_id')
                ->constrained('student_cards')
                ->cascadeOnDelete();

            $table->foreignId('old_card_id')
                ->nullable()
                ->constrained('student_cards')
                ->nullOnDelete();

            $table->foreignId('new_card_id')
                ->nullable()
                ->constrained('student_cards')
                ->nullOnDelete();

            $table->enum('action', [
                'assigned',
                'replaced',
                'lost',
                'damaged',
                'deactivated'
            ]);

            $table->string('reason')->nullable();

            $table->text('remarks')->nullable();

            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('performed_at');

            $table->timestamps();

            $table->index('student_id');
            $table->index('card_id');
            $table->index('performed_at');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_card_histories');
    }
}
