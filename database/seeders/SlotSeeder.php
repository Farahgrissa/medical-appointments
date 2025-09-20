<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slot;
use App\Models\User;

class SlotSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer tous les users avec le rôle "doctor"
        $doctors = User::role('doctor')->get();

        foreach ($doctors as $doctor) {
            // Créer 3 slots pour chaque docteur
            Slot::create([
                'doctor_id' => $doctor->id,
                'date' => now()->addDays(1)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '09:30:00',
                'status' => 'available',
                'slot_type' => 'consultation',
            ]);

            Slot::create([
                'doctor_id' => $doctor->id,
                'date' => now()->addDays(1)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '10:30:00',
                'status' => 'available',
                'slot_type' => 'consultation',
            ]);

            Slot::create([
                'doctor_id' => $doctor->id,
                'date' => now()->addDays(2)->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '14:30:00',
                'status' => 'available',
                'slot_type' => 'consultation',
            ]);
        }
    }
}
