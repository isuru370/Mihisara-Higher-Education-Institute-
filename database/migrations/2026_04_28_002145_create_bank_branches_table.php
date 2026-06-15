<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankBranchesTable extends Migration
{
    public function up()
    {
        Schema::create('bank_branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bank_id')
                ->constrained('banks')
                ->cascadeOnDelete();

            $table->string('branch_name', 150);
            $table->string('branch_code', 50);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('bank_id');
            $table->index('branch_name');
            $table->index(['bank_id', 'branch_code'], 'bank_branch_code_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bank_branches');
    }
}
