<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizersTable extends Migration
{
    public function up()
    {
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique(); // ORG001
            $table->string('name', 150);

            $table->string('mobile', 20)->nullable();
            $table->string('email', 150)->nullable();

            $table->string('nic', 20)->nullable()->unique();

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index(['name', 'is_active']);
            $table->index('mobile');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizers');
    }
}