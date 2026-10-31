<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\SlotStatus;
use App\Models\Appointment;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::factory()->doctor()->create();
    }

    private function slot(array $attributes = []): Slot
    {
        return Slot::create([
            'doctor_id' => $this->doctor->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => SlotStatus::Available,
            'slot_type' => 'consultation',
            ...$attributes,
        ]);
    }

    private function book(User $patient, Slot $slot)
    {
        return $this->actingAs($patient)->post(route('patient.bookAppointment'), [
            'slot_id' => $slot->id,
            'reason' => 'Check-up',
        ]);
    }

    // ===== Browsing =====

    public function test_patient_sees_doctors_and_their_available_slots(): void
    {
        $this->slot();
        $this->slot(['start_time' => '10:00:00', 'end_time' => '10:30:00', 'status' => SlotStatus::Booked]);
        $this->slot(['date' => now()->subDay()->toDateString()]);
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)
            ->get(route('patient.doctors'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Patient/Doctors')
                ->has('doctors', 1)
                ->where('doctors.0.available_slots_count', 1));

        $this->actingAs($patient)
            ->get(route('patient.doctorSlots', $this->doctor))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Patient/DoctorSlots')
                ->has('slots', 1)
                ->where('slots.0.start_time', '09:00:00'));
    }

    public function test_slots_page_of_a_non_doctor_is_not_found(): void
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)
            ->get(route('patient.doctorSlots', $patient))
            ->assertNotFound();
    }

    // ===== Booking =====

    public function test_patient_can_book_an_available_slot(): void
    {
        $slot = $this->slot();
        $patient = User::factory()->patient()->create();

        $this->book($patient, $slot)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('patient.dashboard'));

        $appointment = Appointment::sole();
        $this->assertSame($patient->id, $appointment->patient_id);
        $this->assertSame($this->doctor->id, $appointment->doctor_id);
        $this->assertSame(AppointmentStatus::Pending, $appointment->status);
        $this->assertSame('09:00:00', $appointment->start_time);
        $this->assertSame(SlotStatus::Booked, $slot->fresh()->status);
    }

    public function test_a_slot_cannot_be_booked_twice(): void
    {
        $slot = $this->slot();

        $this->book(User::factory()->patient()->create(), $slot)->assertSessionHasNoErrors();
        $this->book(User::factory()->patient()->create(), $slot)->assertSessionHasErrors('slot_id');

        $this->assertSame(1, Appointment::count());
    }

    public function test_a_past_slot_cannot_be_booked(): void
    {
        $slot = $this->slot(['date' => now()->subDay()->toDateString()]);

        $this->book(User::factory()->patient()->create(), $slot)->assertSessionHasErrors('slot_id');

        $this->assertSame(0, Appointment::count());
    }

    public function test_patient_cannot_book_two_overlapping_appointments(): void
    {
        $patient = User::factory()->patient()->create();
        $otherDoctor = User::factory()->doctor()->create();
        $first = $this->slot();
        $overlapping = $this->slot(['doctor_id' => $otherDoctor->id, 'start_time' => '09:15:00', 'end_time' => '09:45:00']);

        $this->book($patient, $first)->assertSessionHasNoErrors();
        $this->book($patient, $overlapping)->assertSessionHasErrors('slot_id');

        $this->assertSame(SlotStatus::Available, $overlapping->fresh()->status);
    }

    public function test_doctors_cannot_book_appointments(): void
    {
        $this->book(User::factory()->doctor()->create(), $this->slot())->assertForbidden();
    }

    // ===== Cancel / reschedule =====

    public function test_cancelling_an_appointment_frees_the_slot(): void
    {
        $slot = $this->slot();
        $patient = User::factory()->patient()->create();
        $this->book($patient, $slot);

        $this->actingAs($patient)
            ->patch(route('patient.cancelAppointment', Appointment::sole()))
            ->assertRedirect(route('patient.dashboard'));

        $this->assertSame(AppointmentStatus::Cancelled, Appointment::sole()->status);
        $this->assertSame(SlotStatus::Available, $slot->fresh()->status);

        // Le créneau peut être réservé de nouveau
        $this->book(User::factory()->patient()->create(), $slot)->assertSessionHasNoErrors();
    }

    public function test_patient_can_reschedule_to_another_slot_of_the_same_doctor(): void
    {
        $oldSlot = $this->slot();
        $newSlot = $this->slot(['start_time' => '11:00:00', 'end_time' => '11:30:00']);
        $patient = User::factory()->patient()->create();
        $this->book($patient, $oldSlot);
        $appointment = Appointment::sole();
        $appointment->update(['status' => AppointmentStatus::Confirmed]);

        $this->actingAs($patient)
            ->put(route('patient.updateAppointment', $appointment), ['slot_id' => $newSlot->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('patient.dashboard'));

        $appointment->refresh();
        $this->assertSame($newSlot->id, $appointment->slot_id);
        $this->assertSame('11:00:00', $appointment->start_time);
        $this->assertSame(AppointmentStatus::Pending, $appointment->status);
        $this->assertSame(SlotStatus::Available, $oldSlot->fresh()->status);
        $this->assertSame(SlotStatus::Booked, $newSlot->fresh()->status);
    }

    public function test_patient_cannot_reschedule_to_another_doctors_slot(): void
    {
        $patient = User::factory()->patient()->create();
        $this->book($patient, $this->slot());
        $otherDoctorSlot = $this->slot(['doctor_id' => User::factory()->doctor()->create()->id, 'start_time' => '15:00:00', 'end_time' => '15:30:00']);

        $this->actingAs($patient)
            ->put(route('patient.updateAppointment', Appointment::sole()), ['slot_id' => $otherDoctorSlot->id])
            ->assertSessionHasErrors('slot_id');
    }

    public function test_patient_cannot_reschedule_to_a_booked_slot(): void
    {
        $patient = User::factory()->patient()->create();
        $this->book($patient, $this->slot());
        $bookedSlot = $this->slot(['start_time' => '11:00:00', 'end_time' => '11:30:00', 'status' => SlotStatus::Booked]);

        $this->actingAs($patient)
            ->put(route('patient.updateAppointment', Appointment::sole()), ['slot_id' => $bookedSlot->id])
            ->assertSessionHasErrors('slot_id');
    }

    public function test_edit_page_lists_free_slots_of_the_same_doctor(): void
    {
        $patient = User::factory()->patient()->create();
        $this->book($patient, $this->slot());
        $this->slot(['start_time' => '11:00:00', 'end_time' => '11:30:00']);

        $this->actingAs($patient)
            ->get(route('patient.editAppointment', Appointment::sole()))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Patient/EditAppointment')
                ->has('slots', 1)
                ->where('slots.0.start_time', '11:00:00'));
    }

    // ===== Doctor side =====

    public function test_doctor_can_confirm_a_pending_appointment(): void
    {
        $this->book(User::factory()->patient()->create(), $this->slot());

        $this->actingAs($this->doctor)
            ->patch(route('doctor.confirmAppointment', Appointment::sole()))
            ->assertRedirect();

        $this->assertSame(AppointmentStatus::Confirmed, Appointment::sole()->status);
    }

    public function test_doctor_can_cancel_an_appointment_and_the_slot_is_freed(): void
    {
        $slot = $this->slot();
        $this->book(User::factory()->patient()->create(), $slot);

        $this->actingAs($this->doctor)
            ->patch(route('doctor.cancelAppointment', Appointment::sole()))
            ->assertRedirect();

        $this->assertSame(AppointmentStatus::Cancelled, Appointment::sole()->status);
        $this->assertSame(SlotStatus::Available, $slot->fresh()->status);
    }

    public function test_doctor_cannot_confirm_or_cancel_another_doctors_appointment(): void
    {
        $this->book(User::factory()->patient()->create(), $this->slot());
        $otherDoctor = User::factory()->doctor()->create();

        $this->actingAs($otherDoctor)->patch(route('doctor.confirmAppointment', Appointment::sole()))->assertForbidden();
        $this->actingAs($otherDoctor)->patch(route('doctor.cancelAppointment', Appointment::sole()))->assertForbidden();

        $this->assertSame(AppointmentStatus::Pending, Appointment::sole()->status);
    }

    public function test_doctor_dashboard_lists_upcoming_appointments_and_free_slots(): void
    {
        $this->book(User::factory()->patient()->create(), $this->slot());
        $this->slot(['start_time' => '11:00:00', 'end_time' => '11:30:00']);

        $this->actingAs($this->doctor)
            ->get(route('doctor.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboards/DoctorDashboard')
                ->has('appointments', 1)
                ->has('appointments.0.patient.name')
                ->has('availableSlots', 1));
    }
}
