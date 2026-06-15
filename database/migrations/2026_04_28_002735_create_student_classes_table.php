<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentClassesTable extends Migration
{
    public function up()
    {
        Schema::create('student_classes', function (Blueprint $table) {
            $table->id();

            $table->string('class_name', 150);

            $table->enum('class_type', ['online', 'offline', 'hybrid'])
                ->default('offline');

            $table->string('medium', 50)->default('Sinhala');

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->restrictOnDelete();

            $table->foreignId('grade_id')
                ->constrained('grades')
                ->restrictOnDelete();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_ongoing')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['teacher_id', 'is_active']);
            $table->index(['subject_id', 'grade_id']);
            $table->index(['grade_id', 'is_active']);
            $table->index(['class_type', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_classes');
    }
}