<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherSalariesTable extends Migration
{
    public function up()
    {
        Schema::create('teacher_salaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->year('salary_year');
            $table->unsignedTinyInteger('salary_month');

            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('advance_deduction', 12, 2)->default(0);
            $table->decimal('other_deduction', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);

            $table->enum('status', [
                'pending',
                'approved',
                'paid',
                'cancelled'
            ])->default('paid');

            $table->dateTime('paid_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['teacher_id', 'salary_year', 'salary_month'],
                'unique_teacher_salary_month'
            );

            $table->index(['teacher_id', 'status']);
            $table->index(['salary_year', 'salary_month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher_salaries');
    }
}