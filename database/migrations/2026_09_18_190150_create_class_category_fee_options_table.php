<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassCategoryFeeOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('class_category_fee_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_category_fee_id')
                ->constrained('class_category_fees')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('label', 150);

            $table->decimal('fee', 10, 2)->default(0);

            $table->boolean('is_default')->default(false);

            $table->boolean('is_active')->default(true);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['class_category_fee_id', 'is_active'],
                'ccfo_category_fee_active_idx'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_category_fee_options');
    }
}