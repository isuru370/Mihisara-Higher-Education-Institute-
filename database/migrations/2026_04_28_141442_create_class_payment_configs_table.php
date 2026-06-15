<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassPaymentConfigsTable extends Migration
{
    public function up()
    {
        Schema::create('class_payment_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_class_id')
                ->constrained('student_classes')
                ->cascadeOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            $table->foreignId('organizer_id')
                ->nullable()
                ->constrained('organizers')
                ->nullOnDelete();

            $table->decimal('teacher_percentage', 5, 2);
            $table->decimal('organizer_percentage', 5, 2)->default(0);
            $table->decimal('institution_percentage', 5, 2);

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_class_id', 'is_active']);
            $table->index(['teacher_id', 'is_active']);
            $table->index(['organizer_id', 'is_active']);
            $table->index(['effective_from', 'effective_to']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_payment_configs');
    }
}