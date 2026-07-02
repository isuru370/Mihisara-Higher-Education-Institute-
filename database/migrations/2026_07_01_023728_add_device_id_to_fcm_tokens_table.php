<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $table->string('device_id')->nullable()->after('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'device_id']);
            $table->dropColumn('device_id');
        });
    }
};
