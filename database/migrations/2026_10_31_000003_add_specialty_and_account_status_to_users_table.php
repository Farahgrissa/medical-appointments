<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('specialty', 100)->nullable()->after('gender')->comment('Spécialité (médecins)');
            // Compte désactivé par un admin : connexion impossible, réversible
            $table->timestamp('deactivated_at')->nullable()->after('specialty');
            // Suppression du compte par l'utilisateur : les rendez-vous sont conservés
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['specialty', 'deactivated_at']);
        });
    }
};
