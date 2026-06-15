<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalAccessTokensTable extends Migration
{
    public function up()
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();

            $table->morphs('tokenable'); // user / student etc

            $table->string('name');

            $table->string('token', 64)->unique();

            $table->text('abilities')->nullable();

            $table->timestamp('last_used_at')->nullable();

            // 🔥 add expiry (important for mobile security)
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // indexes
            $table->index('last_used_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
}