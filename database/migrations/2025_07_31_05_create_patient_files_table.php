<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('patient_files', function (Blueprint $table) {
            $table->id();
            
            // Relation avec le patient
            $table->foreignId('patient_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate()
                  ->comment('Référence au patient dans users');
            
            // Démographie médicale
            $table->string('blood_type', 10)->nullable()->comment('Groupe sanguin');
            $table->decimal('height', 5, 2)->nullable()->comment('Taille en cm');
            $table->decimal('weight', 5, 2)->nullable()->comment('Poids en kg');
            
            // Données médicales
            $table->json('allergies')->nullable()->comment('Liste des allergies en JSON');
            $table->json('current_medications')->nullable()->comment('Médicaments actuels en JSON');
            $table->text('medical_history')->fulltext()->comment('Antécédents médicaux');
            $table->text('chronic_conditions')->fulltext()->comment('Pathologies chroniques');
            $table->text('surgical_history')->nullable()->comment('Antécédents chirurgicaux');
            $table->text('family_history')->nullable()->comment('Antécédents familiaux');
            
            // Métadonnées
            $table->timestamps();
            $table->softDeletes()->comment('Archivage des dossiers');
            
            // Index
            $table->index('patient_id');
            $table->index('blood_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_files');
    }
};