<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_type_id')
                ->constrained('user_types')
                ->cascadeOnDelete();

            $table->foreignId('page_id')
                ->constrained('pages')
                ->cascadeOnDelete();

            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_type_id', 'page_id'], 'unique_user_type_page');

            $table->index(['user_type_id', 'is_active']);
            $table->index(['page_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
    }
}