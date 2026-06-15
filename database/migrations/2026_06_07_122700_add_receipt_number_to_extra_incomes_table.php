<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceiptNumberToExtraIncomesTable extends Migration
{
    public function up()
    {
        Schema::table('extra_incomes', function (Blueprint $table) {

            $table->string('receipt_number', 100)
                ->nullable()
                ->unique()
                ->after('status');
        });
    }

    public function down()
    {
        Schema::table('extra_incomes', function (Blueprint $table) {

            $table->dropColumn('receipt_number');
        });
    }
}