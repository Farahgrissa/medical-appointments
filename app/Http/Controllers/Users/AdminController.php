<?php

namespace App\Http\Controllers\Users;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct(private AccountService $accounts)
    {
    }

    // ===== DASHBOARD (statistiques) =====
    public function dashboard()
    {
        $byStatus = Appointment::query()->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (AppointmentStatus $status) => (int) ($byStatus[$status->value] ?? 0);
        $total = (int) $byStatus->sum();
        $closed = $count(AppointmentStatus::Completed) + $count(AppointmentStatus::NoShow);

        return Inertia::render('Dashboards/AdminDashboard', [
            'stats' => [
                'patients' => User::role('patient')->count(),
                'doctors' => User::role('doctor')->count(),
                'deactivatedUsers' => User::whereNotNull('deactivated_at')->count(),
                'appointments' => $total,
                'upcomingAppointments' => Appointment::active()->upcoming()->count(),
                'byStatus' => collect(AppointmentStatus::cases())
                    ->map(fn (AppointmentStatus $status) => [
                        'status' => $status->value,
                        'label' => $status->label(),
                        'total' => $count($status),
                    ])->values(),
                // Taux en pourcentage, null tant qu'il n'y a pas de données
                'cancellationRate' => $total ? round($count(AppointmentStatus::Cancelled) / $total * 100, 1) : null,
                'noShowRate' => $closed ? round($count(AppointmentStatus::NoShow) / $closed * 100, 1) : null,
            ],
            'latestUsers' => User::with('roles:id,name')
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'created_at', 'deactivated_at']),
        ]);
    }

    // ===== UTILISATEURS (modération) =====
    public function users(Request $request)
    {
        $filters = $request->only(['search', 'role', 'state']);

        $users = User::with('roles:id,name')
            ->when(in_array($filters['role'] ?? null, ['patient', 'doctor', 'admin'], true), fn ($q) => $q->role($filters['role']))
            ->when(($filters['state'] ?? null) === 'active', fn ($q) => $q->notDeactivated())
            ->when(($filters['state'] ?? null) === 'deactivated', fn ($q) => $q->whereNotNull('deactivated_at'))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(15, ['id', 'name', 'email', 'specialty', 'created_at', 'deactivated_at'])
            ->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function deactivate(User $user)
    {
        $this->authorize('moderate', $user);

        $this->accounts->deactivate($user);

        return back()->with('success', "{$user->name}'s account has been deactivated.");
    }

    public function reactivate(User $user)
    {
        $this->authorize('moderate', $user);

        $this->accounts->reactivate($user);

        return back()->with('success', "{$user->name}'s account has been reactivated.");
    }

    // ===== CRÉER UN MÉDECIN =====
    public function createDoctor()
    {
        $this->authorize('create', User::class);

        return Inertia::render('Admin/CreateDoctor');
    }

    public function storeDoctor(StoreDoctorRequest $request)
    {
        $doctor = User::create([
            ...$request->validated(),
            // Mot de passe aléatoire : le médecin choisit le sien via le lien envoyé par email
            'password' => Hash::make(Str::random(40)),
        ]);
        $doctor->forceFill(['email_verified_at' => now()])->save();
        $doctor->assignRole('doctor');

        Password::sendResetLink(['email' => $doctor->email]);

        return redirect()->route('admin.users', ['role' => 'doctor'])
            ->with('success', "Doctor account created. A link to set the password was sent to {$doctor->email}.");
    }
}
