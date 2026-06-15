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
        Schema::create('student_id_cards', function (Blueprint $table) {

            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->string('card_no', 50)->unique();


            $table->enum('status', [
                'pending',
                'downloaded',
                'active',
                'deleted',
            ])->default('pending')->index();


            $table->enum('registration_status', [
                'pending',
                'incomplete',
                'completed',
            ])->default('pending')->index();

            $table->decimal('student_fee', 10, 2)->default(350.00);

            $table->decimal('print_cost', 10, 2)->default(90.00);

            $table->decimal('profit', 10, 2)->default(260.00);


            $table->boolean('is_reissue')
                ->default(false)
                ->index();

            $table->foreignId('reissue_from_id')
                ->nullable()
                ->constrained('student_id_cards')
                ->nullOnDelete();

            $table->timestamp('downloaded_at')
                ->nullable()
                ->index();

            $table->timestamp('deleted_at')
                ->nullable()
                ->index();

            $table->timestamps();

            $table->index(['student_id', 'status']);

            $table->index([
                'student_id',
                'registration_status'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_id_cards');
    }
};
