<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create()
    {
        // Return the registration page (Inertia Vue component)
        return Inertia::render('Auth/Register'); 
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request)
    {
        // Validate the fields
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:doctor,patient'], 
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign the role using Spatie
        $user->assignRole($request->role);

        // Log the user in
        Auth::login($user);

        // Redirect based on role
        return match (true) {
            $user->hasRole('doctor') => redirect()->route('doctor.dashboard'),
            $user->hasRole('patient') => redirect()->route('patient.dashboard'),
            default => redirect()->route('home'),
        };
    }

    /**
     * Delete a user (destroy account).
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Logout the user first
        Auth::logout();

        // Delete the user
        $user->delete();

        // Redirect to home or login page
        return redirect()->route('home')->with('status', 'Account deleted successfully.');
    }
}
