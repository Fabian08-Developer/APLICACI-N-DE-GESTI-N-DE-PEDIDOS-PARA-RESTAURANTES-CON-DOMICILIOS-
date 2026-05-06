<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->string('frequency'); // daily, weekly, monthly, custom
            $table->time('time');
            $table->json('days')->nullable(); // ['L', 'X', 'V']
            $table->json('month_days')->nullable(); // [1, 15]
            $table->json('custom_config')->nullable(); // {value: 2, unit: 'days', start: '...', end: '...'}
            $table->string('method'); // email, whatsapp
            $table->json('recipients')->nullable(); // ['admin@test.com']
            $table->string('whatsapp_number')->nullable();
            $table->json('sections')->nullable(); // ['kpis', 'chart']
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};
