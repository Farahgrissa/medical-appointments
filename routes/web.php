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
    if (Auth::check()) {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('doctor')) {
            return redirect()->route('doctor.dashboard');
        } elseif ($user->hasRole('patient')) {
            return redirect()->route('patient.dashboard');
        } else {
            return Inertia::render('welcome');
        }
    }

    return Inertia::render('welcome');
});

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
});




// ===== Doctor routes =====
Route::prefix('doctor')->middleware(['auth', 'role:doctor'])->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('doctor.dashboard');
    // ajoute tes autres méthodes ici
});

// ===== Admin routes =====
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    // ajoute tes autres méthodes ici
});
