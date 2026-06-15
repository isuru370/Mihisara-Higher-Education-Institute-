<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamsTable extends Migration
{
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();

            $table->string('title', 150);

            $table->foreignId('student_class_id')
                ->constrained('student_classes')
                ->cascadeOnDelete();

            $table->foreignId('class_category_id')
                ->constrained('class_categories')
                ->restrictOnDelete();

            $table->foreignId('class_hall_id')
                ->nullable()
                ->constrained('class_halls')
                ->nullOnDelete();

            $table->date('exam_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->enum('status', [
                'scheduled',
                'ongoing',
                'completed',
                'cancelled',
            ])->default('scheduled');

            $table->text('cancel_reason')->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_class_id', 'exam_date']);
            $table->index(['class_category_id', 'exam_date']);
            $table->index(['status', 'exam_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exams');
    }
}