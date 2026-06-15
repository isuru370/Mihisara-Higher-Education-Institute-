<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePaymentReasonsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_reasons', function (Blueprint $table) {
            $table->id();

            $table->string('reason_code', 50)->unique(); // system key
            $table->string('name', 150); // display name

            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        // 🔥 default data
        DB::table('payment_reasons')->insert([
            [
                'reason_code' => 'advance',
                'name' => 'Advance Payment',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'reason_code' => 'bonus',
                'name' => 'Bonus Payment',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'reason_code' => 'deduction',
                'name' => 'Salary Deduction',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'reason_code' => 'adjustment',
                'name' => 'Manual Adjustment',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('payment_reasons');
    }
}