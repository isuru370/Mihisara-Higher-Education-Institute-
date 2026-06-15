<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentSplitSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_split_snapshots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_id')
                ->constrained('payments')
                ->cascadeOnDelete();

            $table->foreignId('student_class_id')
                ->constrained('student_classes')
                ->cascadeOnDelete();

            $table->foreignId('student_class_enrollment_id')
                ->constrained('student_class_enrollments')
                ->cascadeOnDelete();

            $table->foreignId('class_payment_config_id')
                ->nullable()
                ->constrained('class_payment_configs')
                ->nullOnDelete();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            $table->foreignId('organizer_id')
                ->nullable()
                ->constrained('organizers')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('payment_amount', 12, 2);

            $table->decimal('teacher_percentage', 5, 2);
            $table->decimal('organizer_percentage', 5, 2)->default(0);
            $table->decimal('institution_percentage', 5, 2);

            $table->decimal('teacher_amount', 12, 2);
            $table->decimal('organizer_amount', 12, 2)->default(0);
            $table->decimal('institution_amount', 12, 2);

            $table->dateTime('payment_date');

            $table->timestamps();

            $table->softDeletes();

            $table->unique('payment_id', 'pss_payment_unique');

            $table->index(['student_class_id', 'payment_date'], 'pss_class_payment_date_idx');
            $table->index(['teacher_id', 'payment_date'], 'pss_teacher_payment_date_idx');
            $table->index(['organizer_id', 'payment_date'], 'pss_organizer_payment_date_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_split_snapshots');
    }
};