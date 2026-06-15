<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('custom_id', 50)->unique();

            $table->string('temporary_qr_code', 100)->nullable()->unique();
            $table->dateTime('temporary_qr_code_expire_date')->nullable();

            $table->string('full_name', 150)->nullable();
            $table->string('initial_name', 100);

            $table->string('mobile', 15)->nullable();
            $table->string('whatsapp_mobile', 15)->nullable();
            $table->string('email', 150)->nullable();

            $table->string('nic', 20)->nullable()->unique();
            $table->date('bday')->nullable();

            $table->enum('gender', [
                'male',
                'female',
                'other'
            ])->default('other');

            $table->string('address1', 150)->nullable();
            $table->string('address2', 150)->nullable();
            $table->string('address3', 150)->nullable();

            $table->string('guardian_fname', 100)->nullable();
            $table->string('guardian_lname', 100)->nullable();
            $table->string('guardian_nic', 20)->nullable();
            $table->string('guardian_mobile', 15);

            $table->foreignId('grade_id')
                ->constrained('grades')
                ->restrictOnDelete();

            $table->string('class_type', 50)->nullable()->default('Offline');

            $table->boolean('admission')->default(false);

            $table->string('student_school', 150)->nullable();
            $table->string('img_url');
            $table->date('last_image_update_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('permanent_qr_active')->default(false);
            $table->boolean('student_disable')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'student_disable']);
            $table->index(['grade_id', 'is_active']);
            $table->index('mobile');
            $table->index('guardian_mobile');
        });
    }

    public function down()
    {
        Schema::dropIfExists('students');
    }
};
