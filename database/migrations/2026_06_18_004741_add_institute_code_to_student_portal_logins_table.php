<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_portal_logins', function (Blueprint $table) {

            $table->string('institute_code', 20)
                ->nullable()
                ->after('student_id');

            $table->index('institute_code');
        });
    }

    public function down(): void
    {
        Schema::table('student_portal_logins', function (Blueprint $table) {

            $table->dropIndex(['institute_code']);

            $table->dropColumn('institute_code');
        });
    }
};
