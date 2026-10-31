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
    private function redirectToRoleDashboard(\App\Models\User $user): RedirectResponse
    {
        return redirect()->route($user->dashboardRoute())->with('verified', true);
    }
}
