<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemporaryIdCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporary_id_cards', function (Blueprint $table) {

            $table->id();

            $table->string('temporary_id_number', 50)
                ->unique();

            $table->string('card_number', 50)
                ->unique();

            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('activated_at')->nullable();

            $table->enum('status', [
                'pending',
                'downloaded',
                'issued',
                'active',
                'expired'
            ])->default('pending');

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temporary_id_cards');
    }
}
