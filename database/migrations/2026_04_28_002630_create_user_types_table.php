<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTypesTable extends Migration
{
    public function up()
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();

            $table->string('name', 50)->unique(); // admin, cashier, teacher, etc
            $table->string('code', 50)->unique(); // ADMIN, CASHIER, etc (system use)

            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_types');
    }
}