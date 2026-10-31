<?php
namespace App\Http\Controllers\Users;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class PatientController extends Controller
{
    public function __construct()
    {
        // 'auth' et 'role:patient' sont déjà appliqués dans routes/web.php.
        // L'accès à un rendez-vous précis est vérifié par AppointmentPolicy.
        $this->middleware('permission:view-appointment')->only(['dashboard', 'appointments']);
    }

    // ===== DASHBOARD =====
    public function dashboard(Request $request)
    {/** @var \App\Models\User $user */
        $user = Auth::user();

        $appointments = $user->patientAppointments()
            ->with('doctor')
            ->when($request->input('search'), fn($q, $search) =>
                $q->whereDate('appointment_date', $search)
            )
            ->where('status', '!=', AppointmentStatus::Cancelled)
            ->orderBy('appointment_date', 'asc')
            ->get();

        return Inertia::render('Dashboards/PatientDashboard', [
            'user' => $user,
            'appointments' => $appointments,
            'filters' => $request->only(['search']),
        ]);
    }

    // ===== APPOINTMENTS LIST =====
    public function appointments(Request $request)
    {/** @var \App\Models\User $user */
        $user = Auth::user();

        $appointments = $user->patientAppointments()
            ->with('doctor')
            ->when($request->input('status') === 'upcoming', fn($q) => $q->where('appointment_date', '>=', now()))
            ->when($request->input('status') === 'past', fn($q) => $q->where('appointment_date', '<', now()))
            ->when($request->input('search'), fn($q, $search) =>
                $q->whereHas('doctor', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
            )
            ->orderBy('appointment_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Patient/Appointments', [
            'appointments' => $appointments,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    // ===== EDIT APPOINTMENT =====
    public function editAppointment(Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        return Inertia::render('Patient/EditAppointment', [
            'appointment' => $appointment->load('doctor'),
        ]);
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $appointment->update($request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'slot_id' => 'required|exists:slots,id',
        ]));

        return redirect()->route('patient.appointments')->with('success', 'Appointment updated.');
    }

    // ===== CANCEL APPOINTMENT =====
    public function cancelAppointment(Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        $appointment->update(['status' => AppointmentStatus::Cancelled]);

        return redirect()->route('patient.appointments')->with('success', 'Appointment cancelled.');
    }
}
