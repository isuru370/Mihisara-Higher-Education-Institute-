<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();

            $table->string('bank_name', 150);
            $table->string('bank_code', 50)->unique();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('bank_name');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('banks');
    }
}