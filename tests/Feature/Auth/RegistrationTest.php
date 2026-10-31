<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_as_patient(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('patient.dashboard'));
        $this->assertTrue(User::where('email', 'test@example.com')->first()->hasRole('patient'));
    }

    public function test_users_cannot_register_as_doctor(): void
    {
        $this->post('/register', [
            'name' => 'Fake Doctor',
            'email' => 'fake@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'doctor',
        ]);

        $user = User::where('email', 'fake@example.com')->first();

        $this->assertFalse($user->hasRole('doctor'));
        $this->assertTrue($user->hasRole('patient'));
    }
}
