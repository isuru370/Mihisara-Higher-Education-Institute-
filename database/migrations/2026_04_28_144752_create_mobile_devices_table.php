<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileDevicesTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_devices', function (Blueprint $table) {
            $table->id();

            // 🔥 user / staff
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 🔥 unique device id (mobile side)
            $table->uuid('device_uuid')->unique();

            // device info
            $table->string('device_name')->nullable();
            $table->string('platform')->nullable(); // android / ios
            $table->string('app_version')->nullable();

            // 🔥 auth token (optional)
            $table->string('api_token')->nullable();

            // 🔥 last sync tracking
            $table->timestamp('last_synced_at')->nullable();

            // 🔥 active / logout
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_devices');
    }
}