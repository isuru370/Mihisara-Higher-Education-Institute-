<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentTutesTable extends Migration
{
    public function up()
    {
        Schema::create('student_tutes', function (Blueprint $table) {
            $table->id();

            // student
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            // enrollment (which class + package)
            $table->foreignId('student_class_enrollment_id')
                ->constrained('student_class_enrollments')
                ->cascadeOnDelete();

            $table->date('issued_month');

            // 🔥 issue tracking
            $table->boolean('is_issued')->default(false);

            $table->dateTime('issued_at')->nullable();

            $table->foreignId('issued_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // notes
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'student_id',
                'student_class_enrollment_id',
                'issued_month'
            ], 'student_tute_unique');

            // indexes
            $table->index(['student_id', 'is_issued']);
            $table->index(['student_class_enrollment_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_tutes');
    }
}
