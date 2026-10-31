<?php

namespace App\Services;

use App\Enums\SlotStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Account lifecycle. Accounts are never hard-deleted so that appointments
 * always keep their patient and their doctor.
 */
class AccountService
{
    public function __construct(private AppointmentBookingService $booking)
    {
    }

    /**
     * Soft delete an account: its upcoming appointments are cancelled (slots freed
     * for the other party) and a doctor's free slots are withdrawn.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->withdrawFromFutureAppointments($user);

            $user->delete();
        });
    }

    /**
     * Deactivate an account (reversible). The user can no longer log in and a
     * doctor disappears from the booking pages. Existing appointments are kept:
     * the admin decides what to do with them.
     */
    public function deactivate(User $user): void
    {
        $user->deactivate();
    }

    public function reactivate(User $user): void
    {
        $user->reactivate();
    }

    private function withdrawFromFutureAppointments(User $user): void
    {
        $appointments = $user->patientAppointments()->active()->upcoming()->get()
            ->merge($user->doctorAppointments()->active()->upcoming()->get());

        foreach ($appointments as $appointment) {
            $this->booking->cancel($appointment);
        }

        $user->slots()
            ->where('status', SlotStatus::Available)
            ->upcoming()
            ->update(['status' => SlotStatus::Cancelled]);
    }
}
