<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickPhotosTable extends Migration
{
    public function up()
    {
        Schema::create('quick_photos', function (Blueprint $table) {
            $table->id();

            $table->string('custom_id', 50)->nullable()->unique();

            $table->string('image_path', 255);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['custom_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quick_photos');
    }
}
