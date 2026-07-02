<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->text('endpoint')->unique();
            $table->string('public_key', 512)->nullable();
            $table->string('auth_token', 512)->nullable();
            $table->string('content_encoding', 32)->default('aesgcm');
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('usuarios')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
