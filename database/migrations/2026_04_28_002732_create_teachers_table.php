<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            $table->string('custom_id', 50)->unique();

            $table->string('full_name', 150);
            $table->string('initials', 20);

            $table->string('email', 150)->unique();
            $table->string('mobile', 20)->index();

            $table->string('nic', 20)->unique();

            $table->date('bday');

            $table->enum('gender', ['male', 'female', 'other']);

            $table->string('address1', 150);
            $table->string('address2', 150)->nullable();
            $table->string('address3', 150)->nullable();

            $table->boolean('is_active')->default(true);

            $table->longText('graduation_details')->nullable();
            $table->longText('experience')->nullable();

            $table->string('account_number', 50)->nullable();

            $table->foreignId('bank_branch_id')
                ->nullable()
                ->constrained('bank_branches')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // indexes
            $table->index(['is_active']);
            $table->index(['full_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('teachers');
    }
}