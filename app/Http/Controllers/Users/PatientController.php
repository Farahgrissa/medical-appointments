<?php
namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookAppointmentRequest;
use App\Http\Requests\RescheduleAppointmentRequest;
use App\Models\User;
use App\Services\AppointmentBookingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class PatientController extends Controller
{
    public function __construct(private AppointmentBookingService $booking)
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
            ->with('doctor:id,name')
            ->when($request->input('search'), fn($q, $search) =>
                $q->whereDate('appointment_date', $search)
            )
            ->active()
            ->upcoming()
            ->get();

        return Inertia::render('Dashboards/PatientDashboard', [
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
            ->when($request->input('status') === 'upcoming', fn($q) => $q->whereDate('appointment_date', '>=', now()->toDateString()))
            ->when($request->input('status') === 'past', fn($q) => $q->whereDate('appointment_date', '<', now()->toDateString()))
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

    // ===== FIND A DOCTOR =====
    public function doctors(Request $request)
    {
        $doctors = User::role('doctor')
            ->select('id', 'name')
            ->withCount(['slots as available_slots_count' => fn ($q) => $q->available()])
            ->when($request->input('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return Inertia::render('Patient/Doctors', [
            'doctors' => $doctors,
            'filters' => $request->only(['search']),
        ]);
    }

    public function doctorSlots(User $doctor)
    {
        abort_unless($doctor->hasRole('doctor'), 404);

        return Inertia::render('Patient/DoctorSlots', [
            'doctor' => $doctor->only('id', 'name'),
            'slots' => $doctor->slots()->available()->orderBy('date')->orderBy('start_time')->get(),
        ]);
    }

    // ===== BOOK APPOINTMENT =====
    public function bookAppointment(BookAppointmentRequest $request)
    {
        $this->booking->book($request->user(), $request->validated('slot_id'), $request->validated('reason'));

        return redirect()->route('patient.dashboard')->with('success', 'Appointment booked. The doctor will confirm it soon.');
    }

    // ===== EDIT APPOINTMENT =====
    public function editAppointment(Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        return Inertia::render('Patient/EditAppointment', [
            'appointment' => $appointment->load('doctor:id,name'),
            // Créneaux libres du même médecin
            'slots' => $appointment->doctor->slots()->available()->orderBy('date')->orderBy('start_time')->get(),
        ]);
    }

    public function updateAppointment(RescheduleAppointmentRequest $request, Appointment $appointment)
    {
        $this->booking->reschedule($appointment, $request->validated('slot_id'));

        return redirect()->route('patient.dashboard')->with('success', 'Appointment rescheduled.');
    }

    // ===== CANCEL APPOINTMENT =====
    public function cancelAppointment(Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        $this->booking->cancel($appointment);

        return redirect()->route('patient.dashboard')->with('success', 'Appointment cancelled.');
    }
}
