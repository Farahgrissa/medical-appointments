<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Before the appointment starts: confirm, reschedule, cancel.
 * Once it has started: only the doctor can close it (completed / no-show).
 */
class AppointmentPolicy
{
    /**
     * A patient sees their own appointments, a doctor sees the ones booked with them.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->can('view-appointment')
            && $this->isParticipant($user, $appointment);
    }

    /**
     * Only patients book appointments.
     */
    public function create(User $user): bool
    {
        return $user->can('create-appointment') && $user->hasRole('patient');
    }

    /**
     * The doctor of the appointment confirms it while it is pending.
     */
    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->can('edit-appointment')
            && $user->id === $appointment->doctor_id
            && $appointment->isPending()
            && ! $appointment->hasStarted();
    }

    /**
     * Only the patient who booked the appointment can reschedule it, while it is still open.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can('edit-appointment')
            && $user->id === $appointment->patient_id
            && $this->isOpenAndUpcoming($appointment);
    }

    /**
     * The patient or the doctor of the appointment can cancel it, while it is still open.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->isParticipant($user, $appointment)
            && $this->isOpenAndUpcoming($appointment);
    }

    /**
     * After the start time, the doctor records the outcome: completed or no-show.
     */
    public function close(User $user, Appointment $appointment): bool
    {
        return $user->can('edit-appointment')
            && $user->id === $appointment->doctor_id
            && $appointment->status->isOpen()
            && $appointment->hasStarted();
    }

    private function isOpenAndUpcoming(Appointment $appointment): bool
    {
        return $appointment->status->isOpen() && ! $appointment->hasStarted();
    }

    private function isParticipant(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->patient_id
            || $user->id === $appointment->doctor_id;
    }
}
