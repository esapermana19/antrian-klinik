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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id('medrec_id');
            $table->foreignId('queue_id')->constrained('queues', 'queue_id')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->restrictOnDelete();
            $table->string('medrec_code', 26)->unique();
            $table->string('main_complaint', 50);
            $table->string('blood_pressure', 15);
            $table->string('temperature', 5);
            $table->integer('weight');
            $table->string('diagnosis', 50);
            $table->string('notes_on_treatment', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
