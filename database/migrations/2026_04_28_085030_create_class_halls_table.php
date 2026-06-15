<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassHallsTable extends Migration
{
    public function up()
    {
        Schema::create('class_halls', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique(); // hall code
            $table->string('hall_name', 150);

            $table->string('hall_type', 50)->nullable(); // AC / Non-AC / Online etc

            $table->decimal('hall_price', 10, 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('hall_name');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_halls');
    }
}