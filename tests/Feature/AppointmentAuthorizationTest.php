<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function createAppointmentFor(User $patient): Appointment
    {
        $doctor = User::factory()->doctor()->create();

        $slot = Slot::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'booked',
        ]);

        return Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'slot_id' => $slot->id,
            'appointment_date' => $slot->date,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => AppointmentStatus::Pending,
        ]);
    }

    public function test_patient_can_cancel_their_own_appointment(): void
    {
        $patient = User::factory()->patient()->create();
        $appointment = $this->createAppointmentFor($patient);

        $this->actingAs($patient)
            ->patch(route('patient.cancelAppointment', $appointment))
            ->assertRedirect(route('patient.appointments'));

        $this->assertSame(AppointmentStatus::Cancelled, $appointment->fresh()->status);
    }

    public function test_patient_cannot_cancel_another_patients_appointment(): void
    {
        $appointment = $this->createAppointmentFor(User::factory()->patient()->create());
        $intruder = User::factory()->patient()->create();

        $this->actingAs($intruder)
            ->patch(route('patient.cancelAppointment', $appointment))
            ->assertForbidden();

        $this->assertSame(AppointmentStatus::Pending, $appointment->fresh()->status);
    }

    public function test_patient_cannot_edit_another_patients_appointment(): void
    {
        $appointment = $this->createAppointmentFor(User::factory()->patient()->create());
        $intruder = User::factory()->patient()->create();

        $this->actingAs($intruder)
            ->get(route('patient.editAppointment', $appointment))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->put(route('patient.updateAppointment', $appointment), [
                'appointment_date' => now()->addDays(2)->toDateString(),
                'slot_id' => $appointment->slot_id,
            ])
            ->assertForbidden();
    }

    public function test_cancelled_appointment_cannot_be_cancelled_again(): void
    {
        $patient = User::factory()->patient()->create();
        $appointment = $this->createAppointmentFor($patient);
        $appointment->update(['status' => AppointmentStatus::Cancelled]);

        $this->actingAs($patient)
            ->patch(route('patient.cancelAppointment', $appointment))
            ->assertForbidden();
    }
}
