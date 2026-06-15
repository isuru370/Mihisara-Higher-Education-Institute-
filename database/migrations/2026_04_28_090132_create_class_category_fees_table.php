<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassCategoryFeesTable extends Migration
{
    public function up()
    {
        Schema::create('class_category_fees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_class_id')
                ->constrained('student_classes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('class_category_id')
                ->constrained('class_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('fee', 10, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['student_class_id', 'class_category_id'],
                'ccf_class_category_unique'
            );

            $table->index(
                ['student_class_id', 'is_active'],
                'ccf_class_active_idx'
            );

            $table->index(
                ['class_category_id', 'is_active'],
                'ccf_category_active_idx'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_category_fees');
    }
}