<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->redirectToRoleDashboard($user);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->redirectToRoleDashboard($user);
    }

    /**
     * Redirect user based on their Spatie role.
     */
    private function redirectToRoleDashboard($user): RedirectResponse
    {
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')->with('verified', true);
        }

        if ($user->hasRole('doctor')) {
            return redirect()->route('doctor.dashboard')->with('verified', true);
        }

        if ($user->hasRole('patient')) {
            return redirect()->route('patient.dashboard')->with('verified', true);
        }

        // fallback if no role assigned
        return redirect()->route('welcome')->with('verified', true);
    }
}
