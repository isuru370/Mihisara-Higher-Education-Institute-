<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('class_categories', function (Blueprint $table) {
            $table->id();

            $table->string('category_name', 100);
            $table->string('code', 50)->unique();

            $table->boolean('is_schedulable')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique('category_name');
            $table->index(['is_active', 'is_schedulable']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_categories');
    }
}