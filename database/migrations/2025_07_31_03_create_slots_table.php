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
    { Schema::create('slots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('doctor_id')
          ->constrained('users')
          ->onDelete('cascade'); // si le médecin est supprimé, ses slots le sont aussi
    $table->date('date'); 
    $table->time('start_time');
    $table->time('end_time');   
    $table->string('status', 20)->default('available')->comment('available, booked, cancelled');
    $table->string('slot_type', 20)->default('consultation');
    $table->timestamps();
});

        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
