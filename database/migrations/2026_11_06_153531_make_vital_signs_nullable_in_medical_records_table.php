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
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('blood_pressure', 15)->nullable()->change();
            $table->string('temperature', 5)->nullable()->change();
            $table->integer('weight')->nullable()->change();
            $table->string('notes_on_treatment', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->string('blood_pressure', 15)->nullable(false)->change();
            $table->string('temperature', 5)->nullable(false)->change();
            $table->integer('weight')->nullable(false)->change();
            $table->string('notes_on_treatment', 100)->nullable(false)->change();
        });
    }
};
