<?php
namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Partage de l'utilisateur connecté avec toutes les pages Inertia
        Inertia::share([
            'auth' => [
                'user' => fn () => Auth::user(),
            ],
            'canLogin' => fn () => Route::has('login'),
        'canRegister' => fn () => Route::has('register'),
        ]);
    }
}
