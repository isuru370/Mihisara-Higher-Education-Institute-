<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id();

            // user email
            $table->string('email')->index();

            // OTP (6 digits)
            $table->string('otp', 255);

            // expiry time
            $table->timestamp('expires_at');

            // verification time
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // indexes
            $table->index(['email', 'otp']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_reset_otps');
    }
}