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
        Schema::create('practice_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->string('schedule_code', 16)->unique();
            $table->date('date');
            $table->enum('session', ['morning', 'afternoon']);
            $table->time('opening_hours');
            $table->time('closing_hours');
            $table->integer('max_quota');
            $table->enum('status', ['open', 'closed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_schedules');
    }
};
