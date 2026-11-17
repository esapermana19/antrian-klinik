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
        Schema::table('practice_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->after('schedule_id');
            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->nullOnDelete();
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->after('schedule_id');
            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->nullOnDelete();
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->after('patient_id');
            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });

        Schema::table('practice_schedules', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });
    }
};
