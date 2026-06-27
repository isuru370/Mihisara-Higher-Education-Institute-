<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_results', function (Blueprint $table) {

            // Add is_absent column after is_updated
            $table->boolean('is_absent')
                ->default(false)
                ->after('is_updated');

            $table->decimal('percentage', 6, 2)
                ->nullable()
                ->after('max_marks');

            $table->string('grade', 10)
                ->nullable()
                ->after('percentage');

            $table->integer('rank')
                ->nullable()
                ->after('grade');

            $table->text('remark')
                ->nullable()
                ->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {

            $table->dropColumn([
                'is_absent',
                'percentage',
                'grade',
                'rank',
                'remark',
            ]);
        });
    }
};