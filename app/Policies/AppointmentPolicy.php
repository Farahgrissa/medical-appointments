<?php

namespace App\Policies;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;

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
     * Only the patient who booked the appointment can reschedule it, and not once cancelled.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can('edit-appointment')
            && $user->id === $appointment->patient_id
            && $appointment->status !== AppointmentStatus::Cancelled;
    }

    /**
     * The patient or the doctor of the appointment can cancel it.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->isParticipant($user, $appointment)
            && $appointment->status !== AppointmentStatus::Cancelled;
    }

    private function isParticipant(User $user, Appointment $appointment): bool
    {
        return $user->id === $appointment->patient_id
            || $user->id === $appointment->doctor_id;
    }
}
