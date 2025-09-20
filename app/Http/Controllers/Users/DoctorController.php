<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class DoctorController extends Controller
{
    // Dashboard
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $upcomingAppointments = $user->doctorAppointments()
            ->where('appointment_date', '>=', now())
            ->get();

        return Inertia::render('Dashboards/DoctorDashboard', [
            'user' => $user,
            'upcomingAppointments' => $upcomingAppointments,
        ]);
    }

    // Voir ses rendez-vous
    public function appointments(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $appointments = $user->doctorAppointments()
            ->with('patient')
            ->when($request->input('status') === 'upcoming', fn($q) => $q->where('appointment_date', '>=', now()))
            ->when($request->input('status') === 'past', fn($q) => $q->where('appointment_date', '<', now()))
            ->when($request->input('search'), fn($q, $search) => 
                $q->whereHas('patient', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
            )
            ->orderBy('appointment_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Doctor/Appointments', [
            'appointments' => $appointments,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    // Profil
    public function profile()
    {
        return Inertia::render('Doctor/Profile', [
            'user' => Auth::user(),
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
