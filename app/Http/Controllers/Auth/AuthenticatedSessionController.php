<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    /**
     * Affiche la page de connexion.
     */
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Traite la connexion de l'utilisateur.
     */
    public function store(Request $request)
    {/** @var \App\Models\User $user */
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Compte désactivé par un admin (les comptes supprimés, eux, ne peuvent pas s'authentifier)
            if ($user->isDeactivated()) {
                Auth::guard('web')->logout();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the clinic.',
                ]);
            }

            $request->session()->regenerate();

            // Redirection selon le rôle
            return redirect()->route($user->dashboardRoute());
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
