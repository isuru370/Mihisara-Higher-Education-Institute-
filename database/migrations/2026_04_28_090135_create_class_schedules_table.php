<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassSchedulesTable extends Migration
{
    public function up()
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_schedule_pattern_id')
                ->nullable()
                ->constrained('class_schedule_patterns')
                ->nullOnDelete();

            $table->foreignId('student_class_id')
                ->constrained('student_classes')
                ->cascadeOnDelete();

            $table->foreignId('class_category_fee_id')
                ->constrained('class_category_fees')
                ->restrictOnDelete();

            $table->foreignId('class_hall_id')
                ->nullable()
                ->constrained('class_halls')
                ->nullOnDelete();

            $table->date('class_date');

            $table->time('start_time');
            $table->time('end_time');

            $table->enum('day_of_week', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
            ]);

            $table->enum('status', [
                'scheduled',
                'ongoing',
                'completed',
                'cancelled',
            ])->default('scheduled');

            $table->boolean('is_active')->default(true);

            $table->text('cancel_reason')->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'student_class_id',
                'class_category_fee_id',
                'class_date',
                'start_time',
                'end_time',
            ], 'cs_unique_slot');

            $table->index(['class_schedule_pattern_id', 'class_date'], 'cs_pattern_date_idx');
            $table->index(['student_class_id', 'class_date'], 'cs_class_date_idx');
            $table->index(['class_category_fee_id', 'class_date'], 'cs_category_fee_date_idx');
            $table->index(['class_hall_id', 'class_date'], 'cs_hall_date_idx');
            $table->index(['status', 'class_date'], 'cs_status_date_idx');
            $table->index(['is_active', 'class_date'], 'cs_active_date_idx');
            $table->index(['day_of_week', 'class_date'], 'cs_day_date_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_schedules');
    }
}
