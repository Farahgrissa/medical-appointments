<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PatientFile;

class PatientFileSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer un patient
        $patient = User::role('patient')->first();

        if ($patient) {
            PatientFile::firstOrCreate(
                ['patient_id' => $patient->id],
                [
                    'blood_type' => 'A+',
                    'height' => 170.5,
                    'weight' => 65.3,
                    'allergies' => json_encode(['penicillin']),
                    'current_medications' => json_encode(['aspirin']),
                    'medical_history' => 'No major illnesses.',
                    'chronic_conditions' => 'None',
                    'surgical_history' => 'Appendectomy in 2015',
                    'family_history' => 'No hereditary diseases',
                ]
            );
        }
    }
}
