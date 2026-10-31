<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\SlotStatus;
use App\Models\Appointment;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * All the state changes of an appointment and of its slot.
 *
 * Each operation runs in a transaction and locks the slot row (SELECT ... FOR UPDATE),
 * so two patients can never book the same slot at the same time.
 */
class AppointmentBookingService
{
    public function book(User $patient, int $slotId, ?string $reason = null): Appointment
    {
        return DB::transaction(function () use ($patient, $slotId, $reason) {
            $slot = Slot::lockForUpdate()->findOrFail($slotId);

            $this->ensureBookable($slot);
            $this->ensurePatientIsFree($patient, $slot);

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $slot->doctor_id,
                'slot_id' => $slot->id,
                'appointment_date' => $slot->date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'status' => AppointmentStatus::Pending,
                'reason' => $reason,
            ]);

            $slot->update(['status' => SlotStatus::Booked]);

            return $appointment;
        });
    }

    /**
     * Move an appointment to another slot of the same doctor.
     */
    public function reschedule(Appointment $appointment, int $newSlotId): Appointment
    {
        return DB::transaction(function () use ($appointment, $newSlotId) {
            if ($appointment->slot_id === $newSlotId) {
                return $appointment;
            }

            $newSlot = Slot::lockForUpdate()->findOrFail($newSlotId);

            if ($newSlot->doctor_id !== $appointment->doctor_id) {
                throw ValidationException::withMessages([
                    'slot_id' => 'You can only move your appointment to another slot of the same doctor.',
                ]);
            }

            $this->ensureBookable($newSlot);
            $this->ensurePatientIsFree($appointment->patient, $newSlot, $appointment->id);

            $oldSlot = Slot::lockForUpdate()->find($appointment->slot_id);

            $appointment->update([
                'slot_id' => $newSlot->id,
                'appointment_date' => $newSlot->date,
                'start_time' => $newSlot->start_time,
                'end_time' => $newSlot->end_time,
                // The doctor has to confirm the new time again
                'status' => AppointmentStatus::Pending,
            ]);

            $newSlot->update(['status' => SlotStatus::Booked]);

            if ($oldSlot) {
                $this->release($oldSlot);
            }

            return $appointment;
        });
    }

    public function cancel(Appointment $appointment): void
    {
        DB::transaction(function () use ($appointment) {
            $appointment->update(['status' => AppointmentStatus::Cancelled]);

            $slot = Slot::lockForUpdate()->find($appointment->slot_id);

            if ($slot) {
                $this->release($slot);
            }
        });
    }

    public function confirm(Appointment $appointment): void
    {
        $appointment->update(['status' => AppointmentStatus::Confirmed]);
    }

    /**
     * After the appointment: the patient came...
     */
    public function complete(Appointment $appointment): void
    {
        $appointment->update(['status' => AppointmentStatus::Completed]);
    }

    /**
     * ...or did not come.
     */
    public function markNoShow(Appointment $appointment): void
    {
        $appointment->update(['status' => AppointmentStatus::NoShow]);
    }

    /**
     * Make a slot bookable again, unless it is already over.
     */
    private function release(Slot $slot): void
    {
        if ($slot->isPast() || $slot->appointments()->active()->exists()) {
            return;
        }

        $slot->update(['status' => SlotStatus::Available]);
    }

    private function ensureBookable(Slot $slot): void
    {
        if (! $slot->isAvailable()) {
            throw ValidationException::withMessages([
                'slot_id' => 'This slot is no longer available.',
            ]);
        }

        if ($slot->isPast()) {
            throw ValidationException::withMessages([
                'slot_id' => 'This slot is in the past.',
            ]);
        }

        if (! $slot->isBookable()) {
            throw ValidationException::withMessages([
                'slot_id' => 'This doctor is not accepting appointments at the moment.',
            ]);
        }
    }

    /**
     * A patient cannot have two active appointments at overlapping times.
     */
    private function ensurePatientIsFree(User $patient, Slot $slot, ?int $ignoreAppointmentId = null): void
    {
        $overlaps = $patient->patientAppointments()
            ->active()
            ->whereDate('appointment_date', $slot->date->toDateString())
            ->where('start_time', '<', $slot->end_time)
            ->where('end_time', '>', $slot->start_time)
            ->when($ignoreAppointmentId, fn ($q) => $q->whereKeyNot($ignoreAppointmentId))
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'slot_id' => 'You already have an appointment at this time.',
            ]);
        }
    }
}
