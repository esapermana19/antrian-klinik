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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->foreignId('medrec_id')->constrained('medical_records', 'medrec_id')->restrictOnDelete();
            $table->string('invoice_code', 15)->unique();
            $table->decimal('examination_fee', 12, 2);
            $table->decimal('medication_fee', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_status', ['paid', 'unpaid', 'canceled']);
            $table->enum('payment_method', ['cash', 'transfer'])->default('cash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
