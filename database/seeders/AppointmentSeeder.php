<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Slot;
use App\Models\Appointment;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer un patient et un docteur
        $patient = User::role('patient')->first();
        $doctor = User::role('doctor')->first();

        if ($patient && $doctor) {
            // Récupérer un slot disponible pour ce docteur
            $slot = Slot::where('doctor_id', $doctor->id)
                        ->where('status', 'available')
                        ->first();

            if ($slot) {
                Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'slot_id' => $slot->id,
                    'appointment_date' => $slot->date,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'status' => 'pending',
                    'reason' => 'Routine check-up',
                ]);

                // Optionnel : marquer le slot comme réservé
                $slot->update(['status' => 'booked']);
            }
        }
    }
}
