<?php

namespace App\Http\Controllers\Users;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Services\AppointmentBookingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class DoctorController extends Controller
{
    public function __construct(private AppointmentBookingService $booking)
    {
    }

    // Dashboard
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $appointments = $user->doctorAppointments()
            ->active()
            ->upcoming()
            ->with('patient:id,name')
            ->get();

        $availableSlots = $user->slots()
            ->available()
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Dashboards/DoctorDashboard', [
            'appointments' => $appointments,
            'availableSlots' => $availableSlots,
        ]);
    }

    // ===== CONFIRM / CANCEL APPOINTMENT =====
    public function confirmAppointment(Appointment $appointment)
    {
        $this->authorize('confirm', $appointment);

        $this->booking->confirm($appointment);

        return back()->with('success', 'Appointment confirmed.');
    }

    public function cancelAppointment(Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        $this->booking->cancel($appointment);

        return back()->with('success', 'Appointment cancelled.');
    }

    // ===== CLOSE A PAST APPOINTMENT =====
    public function completeAppointment(Appointment $appointment)
    {
        $this->authorize('close', $appointment);

        $this->booking->complete($appointment);

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function noShowAppointment(Appointment $appointment)
    {
        $this->authorize('close', $appointment);

        $this->booking->markNoShow($appointment);

        return back()->with('success', 'Appointment marked as no-show.');
    }

    // Voir ses rendez-vous
    public function appointments(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $filters = $request->only(['search', 'period', 'status']);

        $appointments = $user->doctorAppointments()
            ->with('patient:id,name,deleted_at')
            ->filter($filters, 'patient')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Doctor/Appointments', [
            'appointments' => $appointments,
            'filters' => $filters,
            'statuses' => AppointmentStatus::cases(),
        ]);
    }

    // Profil
    public function profile()
    {
        return Inertia::render('Doctor/Profile', [
            'user' => Auth::user()->only('name', 'email', 'phone', 'date_of_birth', 'specialty'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->update($request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
        ]));

        return redirect()->route('doctor.profile')->with('success', 'Profile updated.');
    }
}
