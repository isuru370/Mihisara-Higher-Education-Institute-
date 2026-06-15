<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentResultsTable extends Migration
{
    public function up()
    {
        Schema::create('student_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnDelete();

            // user delete වුණාට result delete වෙන්න එපා
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // marks
            $table->decimal('marks', 6, 2)->nullable();
            $table->decimal('max_marks', 6, 2)->default(100);

            $table->enum('status', [
                'pending',
                'passed',
                'failed',
                'absent'
            ])->default('pending');

            $table->string('reason', 255)->nullable();

            $table->boolean('is_updated')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // prevent duplicate results
            $table->unique(
                ['student_id', 'exam_id'],
                'unique_student_exam'
            );

            // indexes
            $table->index(['exam_id', 'status']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_results');
    }
}