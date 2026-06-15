<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150); // Admission type (New / Re-registration etc)

            $table->decimal('amount', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('admissions');
    }
}