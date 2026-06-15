<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileSyncLogsTable extends Migration
{
    public function up()
    {
        Schema::create('mobile_sync_logs', function (Blueprint $table) {
            $table->id();

            // 🔥 which device
            $table->foreignId('mobile_device_id')
                ->constrained('mobile_devices')
                ->cascadeOnDelete();

            // 🔥 sync batch id (mobile side generate)
            $table->uuid('sync_batch_uuid')->index();

            // 🔥 what type of sync
            $table->enum('sync_type', [
                'attendance',
                'payment',
                'mixed'
            ]);

            // 🔥 counts
            $table->integer('total_items')->default(0);
            $table->integer('success_items')->default(0);
            $table->integer('failed_items')->default(0);

            // 🔥 status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'partial',
                'failed'
            ])->default('pending');

            // 🔥 error tracking
            $table->text('error_message')->nullable();

            // 🔥 sync time
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            // indexes
            $table->index(['mobile_device_id', 'status']);
            $table->index(['sync_type', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_sync_logs');
    }
}