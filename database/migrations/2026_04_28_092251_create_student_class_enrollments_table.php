<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentClassEnrollmentsTable extends Migration
{
    public function up()
    {
        Schema::create('student_class_enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('student_class_id')
                ->constrained('student_classes')
                ->cascadeOnDelete();

            $table->foreignId('class_category_fee_id')
                ->constrained('class_category_fees')
                ->restrictOnDelete();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_free_card')->default(false);

            // custom fee override
            $table->decimal('custom_fee', 10, 2)->nullable();
            $table->string('custom_fee_reason', 150)->nullable();

            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->string('discount_reason', 150)->nullable();

            $table->date('enrolled_at')->nullable();
            $table->date('left_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'student_id',
                'student_class_id',
                'class_category_fee_id'
            ], 'unique_student_class_fee');

            $table->index(['student_id', 'is_active']);
            $table->index(['student_class_id', 'is_active']);
            $table->index(['class_category_fee_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_class_enrollments');
    }
}
