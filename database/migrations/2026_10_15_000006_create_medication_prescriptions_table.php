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
        Schema::create('medication_prescriptions', function (Blueprint $table) {
            $table->id('prescription_id');
            $table->foreignId('medrec_id')->constrained('medical_records', 'medrec_id')->restrictOnDelete();
            $table->foreignId('medication_id')->constrained('medications', 'medication_id')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('prescription_code', 13)->unique();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->integer('qty');
            $table->string('dosage', 30);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_prescriptions');
    }
};
