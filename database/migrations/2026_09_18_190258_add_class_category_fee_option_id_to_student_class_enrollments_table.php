<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClassCategoryFeeOptionIdToStudentClassEnrollmentsTable extends Migration
{
    public function up()
    {
        Schema::table('student_class_enrollments', function (Blueprint $table) {
            $table->foreignId('class_category_fee_option_id')
                ->nullable()
                ->after('class_category_fee_id')
                ->constrained('class_category_fee_options')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index(
                ['class_category_fee_option_id', 'is_active'],
                'sce_fee_option_active_idx'
            );
        });
    }

    public function down()
    {
        Schema::table('student_class_enrollments', function (Blueprint $table) {
            $table->dropForeign([
                'class_category_fee_option_id'
            ]);

            $table->dropIndex(
                'sce_fee_option_active_idx'
            );

            $table->dropColumn(
                'class_category_fee_option_id'
            );
        });
    }
}