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
        Schema::create('queues', function (Blueprint $table) {
            $table->id('queue_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->restrictOnDelete();
            $table->foreignId('schedule_id')->constrained('practice_schedules', 'schedule_id');
            $table->string('queue_number', 3)->unique();
            $table->enum('session', ['morning', 'afternoon']);
            $table->time('estimate_time');
            $table->enum('queue_status', ['waiting', 'called', 'checking', 'completed', 'cancel'])->default('waiting');
            $table->time('time_register');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
