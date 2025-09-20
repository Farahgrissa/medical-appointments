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
       Schema::create('appointments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('patient_id')
          ->constrained('users')
          ->onDelete('cascade');
    $table->foreignId('doctor_id')
          ->constrained('users')
          ->onDelete('cascade');
    $table->foreignId('slot_id')
          ->constrained('slots')
          ->onDelete('cascade');
    $table->date('appointment_date'); // la date du rendez-vous
    $table->time('start_time');      
    $table->time('end_time'); 
    $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
    $table->text('reason')->nullable(); // motif du rendez-vous
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
