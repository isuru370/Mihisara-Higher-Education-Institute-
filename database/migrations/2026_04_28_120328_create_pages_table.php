<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100); // UI name (Dashboard, Students etc)

            $table->string('route_name', 150)->unique(); // route key (students.index)

            $table->string('module', 100)->nullable(); // grouping (student, payment etc)

            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['module', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pages');
    }
}