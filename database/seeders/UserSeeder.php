<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les rôles existants
        $adminRole = Role::where('name', 'admin')->first();
        $doctorRole = Role::where('name', 'doctor')->first();
        $patientRole = Role::where('name', 'patient')->first();

        // ===== Créer les utilisateurs de test =====

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);


        $doctor1 = User::firstOrCreate(
            ['email' => 'eline@example.com'],
            [
                'name' => 'Dr. Eline',
                'password' => Hash::make('password'),
            ]
        );
        $doctor1->assignRole($doctorRole);


        $doctor2 = User::firstOrCreate(
            ['email' => 'john.@example.com'],
            [
                'name' => 'Dr. John ',
                'password' => Hash::make('password'),
            ]
        );
        $doctor2->assignRole($doctorRole);

        // Patient
        $patient = User::firstOrCreate(
            ['email' => 'patient@test.com'],
            [
                'name' => 'Patient X',
                'password' => Hash::make('password'),
            ]
        );
        $patient->assignRole($patientRole);
    }
}
