<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {

            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('token', 255)->unique();

            $table->string('device_name')->nullable();

            $table->enum('device_type', [
                'android',
                'ios',
            ])->default('android');

            $table->string('app_version')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();

            $table->index('student_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
