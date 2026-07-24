<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_cards', function (Blueprint $table) {
            $table->id();

            // Assigned student (null = card not assigned yet)
            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students')
                ->nullOnDelete();

            // Internal sequence (1,2,3...)
            $table->unsignedBigInteger('card_sequence')->unique();

            // Printed card number (ST000001)
            $table->string('card_number', 20)->unique();

            // QR Code value
            $table->string('qr_code', 100)->unique();

            // Card status
            $table->enum('status', [
                'available',
                'assigned',
                'lost',
                'damaged',
                'inactive',
            ])->default('available');

            // Current active card for the student
            $table->boolean('is_current')->default(false);

            // Assigned date
            $table->timestamp('issued_at')->nullable();

            // If replaced/lost
            $table->timestamp('deactivated_at')->nullable();

            // Admin notes
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('student_id');
            $table->index('status');
            $table->index('card_number');
            $table->index(['student_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_cards');
    }
};
