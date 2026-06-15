<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);
            $table->string('email', 150)->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            $table->foreignId('user_type_id')
                ->constrained('user_types')
                ->restrictOnDelete();

            $table->boolean('is_active')->default(true);

            $table->rememberToken();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_type_id', 'is_active']);
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}