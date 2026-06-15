<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemUsersTable extends Migration
{
    public function up()
    {
        Schema::create('system_users', function (Blueprint $table) {
            $table->id();

            $table->string('custom_id', 50)->unique();

            $table->foreignId('user_id')
                ->unique() // 🔥 one user = one profile
                ->constrained('users')
                ->cascadeOnDelete();

            // profile
            $table->string('full_name', 150);

            $table->string('mobile', 20)->nullable();
            $table->string('nic', 20)->nullable()->unique();

            $table->date('bday')->nullable();

            $table->enum('gender', ['male', 'female', 'other'])
                ->nullable();

            // address
            $table->string('address1', 150)->nullable();
            $table->string('address2', 150)->nullable();
            $table->string('address3', 150)->nullable();

            $table->boolean('is_active')->default(true);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_users');
    }
}