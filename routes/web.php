<?php

use App\Http\Controllers\Users\PatientController;
use App\Http\Controllers\Users\DoctorController;
use App\Http\Controllers\Users\AdminController;
use App\Http\Controllers\Users\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

Route::get('/', function () {
    /** @var User|null $user */
    $user = Auth::user();

    // Un utilisateur connecté avec un rôle va directement sur son tableau de bord
    if ($user && $user->dashboardRoute() !== 'home') {
        return redirect()->route($user->dashboardRoute());
    }

    return Inertia::render('welcome');
})->name('home');

require __DIR__ . '/auth.php';
// Patient routes
Route::prefix('patient')->middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/dashboard', [PatientController::class, 'dashboard'])->name('patient.dashboard');

    // View profile
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('patient.profile');

    // Edit profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('patient.editProfile');

    // Update profile
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('patient.updateProfile');

    // Confirm delete page
    Route::get('/profile/delete', [ProfileController::class, 'delete'])->name('patient.DeleteProfile');

    // Destroy profile
    Route::delete('/profile/destroy', [ProfileController::class, 'destroy'])->name('patient.destroyProfile');

    // Appointments
    Route::get('/appointments', [PatientController::class, 'appointments'])->name('patient.appointments');
    Route::get('/appointments/{appointment}/edit', [PatientController::class, 'editAppointment'])->name('patient.editAppointment');
    Route::put('/appointments/{appointment}', [PatientController::class, 'updateAppointment'])->name('patient.updateAppointment');
    Route::patch('/appointments/{appointment}/cancel', [PatientController::class, 'cancelAppointment'])->name('patient.cancelAppointment');
});

// ===== Doctor routes =====
Route::prefix('doctor')->middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('doctor.dashboard');

    // Appointments
    Route::get('/appointments', [DoctorController::class, 'appointments'])->name('doctor.appointments');

    // Profile
    Route::get('/profile', [DoctorController::class, 'profile'])->name('doctor.profile');
    Route::put('/profile', [DoctorController::class, 'updateProfile'])->name('doctor.updateProfile');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('doctor.destroyProfile');
});

// ===== Admin routes =====
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    // ajoute tes autres méthodes ici
});
