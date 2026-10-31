<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An appointment must never disappear: deleting a user or a slot that still
     * has appointments is refused by the database instead of cascading.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['slot_id']);

            $table->foreign('patient_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('doctor_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('slot_id')->references('id')->on('slots')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['slot_id']);

            $table->foreign('patient_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('slot_id')->references('id')->on('slots')->cascadeOnDelete();
        });
    }
};
