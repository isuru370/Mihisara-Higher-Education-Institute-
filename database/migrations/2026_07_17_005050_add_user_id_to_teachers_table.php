<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToTeachersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('teachers', 'user_id')) {

            Schema::table('teachers', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->unique()
                    ->after('custom_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            });

        }
    }

    public function down()
    {
        if (Schema::hasColumn('teachers', 'user_id')) {

            Schema::table('teachers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });

        }
    }
}