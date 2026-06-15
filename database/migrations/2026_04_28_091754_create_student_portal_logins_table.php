<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentPortalLoginsTable extends Migration
{
    public function up()
    {
        Schema::create('student_portal_logins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->unique()
                ->constrained('students')
                ->cascadeOnDelete();

            $table->string('username', 100)->unique();
            $table->string('password'); // hashed password

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);

            $table->string('otp', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_verified']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_portal_logins');
    }
}