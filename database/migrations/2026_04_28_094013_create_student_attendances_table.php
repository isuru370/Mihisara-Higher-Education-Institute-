<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();

            // 🔥 Local device UUID (offline support)
            $table->uuid('local_uuid')->nullable()->unique();

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('class_schedule_id')
                ->constrained('class_schedules')
                ->cascadeOnDelete();

            $table->foreignId('student_class_enrollment_id')
                ->nullable()
                ->constrained('student_class_enrollments')
                ->nullOnDelete();

            $table->dateTime('attended_at');

            $table->enum('mark_method', [
                'qr_mobile',
                'qr_web',
                'manual_mobile',
                'manual_web'
            ])->default('qr_mobile');

            $table->foreignId('marked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_synced')->default(true);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate attendance
            $table->unique([
                'student_id',
                'class_schedule_id'
            ], 'unique_student_schedule_attendance');

            $table->index(['student_id', 'attended_at']);
            $table->index(['class_schedule_id', 'attended_at']);
            $table->index(['is_synced']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_attendances');
    }
}