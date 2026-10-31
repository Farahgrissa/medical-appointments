<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\SlotStatus;
use App\Models\Appointment;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Statuts completed / no_show, actions bloquées sur les rendez-vous passés,
 * suppression des comptes en soft delete : un rendez-vous ne disparaît jamais.
 */
class AppointmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $doctor;

    private User $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = User::factory()->doctor()->create();
        $this->patient = User::factory()->patient()->create();
    }

    private function appointment(string $date, AppointmentStatus $status = AppointmentStatus::Confirmed, string $start = '09:00:00'): Appointment
    {
        $end = now()->setTimeFromTimeString($start)->addMinutes(30)->format('H:i:s');

        $slot = Slot::create([
            'doctor_id' => $this->doctor->id,
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => SlotStatus::Booked,
        ]);

        return Appointment::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'slot_id' => $slot->id,
            'appointment_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $status,
        ]);
    }

    private function yesterday(): string
    {
        return now()->subDay()->toDateString();
    }

    private function tomorrow(): string
    {
        return now()->addDay()->toDateString();
    }

    // ===== completed / no_show =====

    public function test_doctor_marks_a_past_appointment_as_completed(): void
    {
        $appointment = $this->appointment($this->yesterday());

        $this->actingAs($this->doctor)
            ->patch(route('doctor.completeAppointment', $appointment))
            ->assertRedirect();

        $this->assertSame(AppointmentStatus::Completed, $appointment->fresh()->status);
    }

    public function test_doctor_marks_a_past_appointment_as_no_show(): void
    {
        $appointment = $this->appointment($this->yesterday(), AppointmentStatus::Pending);

        $this->actingAs($this->doctor)
            ->patch(route('doctor.noShowAppointment', $appointment))
            ->assertRedirect();

        $this->assertSame(AppointmentStatus::NoShow, $appointment->fresh()->status);
    }

    public function test_a_future_appointment_cannot_be_closed(): void
    {
        $appointment = $this->appointment($this->tomorrow());

        $this->actingAs($this->doctor)->patch(route('doctor.completeAppointment', $appointment))->assertForbidden();
        $this->actingAs($this->doctor)->patch(route('doctor.noShowAppointment', $appointment))->assertForbidden();
    }

    public function test_a_closed_appointment_cannot_be_closed_again(): void
    {
        $appointment = $this->appointment($this->yesterday(), AppointmentStatus::Completed);

        $this->actingAs($this->doctor)->patch(route('doctor.noShowAppointment', $appointment))->assertForbidden();
    }

    public function test_another_doctor_cannot_close_the_appointment(): void
    {
        $appointment = $this->appointment($this->yesterday());

        $this->actingAs(User::factory()->doctor()->create())
            ->patch(route('doctor.completeAppointment', $appointment))
            ->assertForbidden();
    }

    // ===== Actions bloquées sur les rendez-vous passés =====

    public function test_past_appointments_cannot_be_confirmed_cancelled_or_rescheduled(): void
    {
        $appointment = $this->appointment($this->yesterday(), AppointmentStatus::Pending);
        $freeSlot = Slot::create([
            'doctor_id' => $this->doctor->id,
            'date' => $this->tomorrow(),
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'status' => SlotStatus::Available,
        ]);

        $this->actingAs($this->doctor)->patch(route('doctor.confirmAppointment', $appointment))->assertForbidden();
        $this->actingAs($this->doctor)->patch(route('doctor.cancelAppointment', $appointment))->assertForbidden();
        $this->actingAs($this->patient)->patch(route('patient.cancelAppointment', $appointment))->assertForbidden();
        $this->actingAs($this->patient)->get(route('patient.editAppointment', $appointment))->assertForbidden();
        $this->actingAs($this->patient)
            ->put(route('patient.updateAppointment', $appointment), ['slot_id' => $freeSlot->id])
            ->assertForbidden();

        $this->assertSame(AppointmentStatus::Pending, $appointment->fresh()->status);
    }

    public function test_an_appointment_that_started_earlier_today_is_past(): void
    {
        $this->travelTo(now()->setTime(12, 0));
        $appointment = $this->appointment(now()->toDateString(), AppointmentStatus::Confirmed, '10:00:00');

        $this->assertTrue($appointment->hasStarted());
        $this->actingAs($this->patient)->patch(route('patient.cancelAppointment', $appointment))->assertForbidden();
        $this->actingAs($this->doctor)->patch(route('doctor.completeAppointment', $appointment))->assertRedirect();
    }

    // ===== Pages de listes =====

    public function test_patient_appointments_page_lists_and_filters_history(): void
    {
        $this->appointment($this->yesterday(), AppointmentStatus::Completed);
        $this->appointment($this->tomorrow());

        $this->actingAs($this->patient)
            ->get(route('patient.appointments'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Patient/Appointments')
                ->has('appointments.data', 2)
                ->has('statuses', 5));

        $this->actingAs($this->patient)
            ->get(route('patient.appointments', ['period' => 'past']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('appointments.data', 1)
                ->where('appointments.data.0.status', 'completed')
                ->where('appointments.data.0.has_started', true));
    }

    public function test_doctor_appointments_page_filters_by_status(): void
    {
        $this->appointment($this->yesterday(), AppointmentStatus::NoShow);
        $this->appointment($this->tomorrow());

        $this->actingAs($this->doctor)
            ->get(route('doctor.appointments', ['status' => 'no_show']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Doctor/Appointments')
                ->has('appointments.data', 1)
                ->where('appointments.data.0.status', 'no_show'));
    }

    public function test_doctor_profile_page_is_displayed(): void
    {
        $this->actingAs($this->doctor)
            ->get(route('doctor.profile'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Doctor/Profile')
                ->where('user.specialty', $this->doctor->specialty));
    }

    // ===== Soft delete : un rendez-vous ne disparaît jamais =====

    public function test_deleting_a_patient_account_keeps_history_and_cancels_upcoming_appointments(): void
    {
        $past = $this->appointment($this->yesterday(), AppointmentStatus::Completed);
        $upcoming = $this->appointment($this->tomorrow());

        $this->actingAs($this->patient)->delete(route('patient.destroyProfile'))->assertRedirect('/');

        $this->assertSoftDeleted($this->patient);
        $this->assertSame(AppointmentStatus::Completed, $past->fresh()->status);
        $this->assertSame(AppointmentStatus::Cancelled, $upcoming->fresh()->status);
        $this->assertSame(SlotStatus::Available, $upcoming->slot()->first()->status);

        // Le médecin voit toujours le nom du patient dans son historique
        $this->assertSame($this->patient->name, $past->fresh()->patient->name);
    }

    public function test_deleting_a_doctor_account_cancels_upcoming_appointments_and_withdraws_free_slots(): void
    {
        $upcoming = $this->appointment($this->tomorrow());
        $freeSlot = Slot::create([
            'doctor_id' => $this->doctor->id,
            'date' => $this->tomorrow(),
            'start_time' => '15:00:00',
            'end_time' => '15:30:00',
            'status' => SlotStatus::Available,
        ]);

        $this->actingAs($this->doctor)->delete(route('doctor.destroyProfile'))->assertRedirect('/');

        $this->assertSoftDeleted($this->doctor);
        $this->assertSame(AppointmentStatus::Cancelled, $upcoming->fresh()->status);
        $this->assertSame(SlotStatus::Cancelled, $freeSlot->fresh()->status);
        $this->assertSame($this->doctor->name, $upcoming->fresh()->doctor->name);
    }

    public function test_deleting_a_slot_with_history_withdraws_it_instead(): void
    {
        $appointment = $this->appointment($this->tomorrow(), AppointmentStatus::Cancelled);
        $slot = $appointment->slot()->first();
        $slot->update(['status' => SlotStatus::Available]);

        $this->actingAs($this->doctor)->delete(route('doctor.destroySlot', $slot))->assertRedirect();

        $this->assertSame(SlotStatus::Cancelled, $slot->fresh()->status);
        $this->assertNotNull($appointment->fresh());
    }

    public function test_the_database_refuses_to_hard_delete_a_user_with_appointments(): void
    {
        $this->appointment($this->yesterday(), AppointmentStatus::Completed);

        $this->expectException(QueryException::class);

        $this->patient->forceDelete();
    }
}
