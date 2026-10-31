<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/patient/dashboard')->assertRedirect('/login');
        $this->get('/doctor/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_patient_can_visit_patient_dashboard()
    {
        $this->actingAs(User::factory()->patient()->create())
            ->get('/patient/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboards/PatientDashboard'));
    }

    public function test_doctor_can_visit_doctor_dashboard()
    {
        $this->actingAs(User::factory()->doctor()->create())
            ->get('/doctor/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboards/DoctorDashboard'));
    }

    public function test_admin_can_visit_admin_dashboard()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboards/AdminDashboard'));
    }

    public function test_users_cannot_visit_another_roles_dashboard()
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)->get('/doctor/dashboard')->assertForbidden();
        $this->actingAs($patient)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_home_redirects_authenticated_users_to_their_dashboard()
    {
        $this->actingAs(User::factory()->doctor()->create())
            ->get('/')
            ->assertRedirect(route('doctor.dashboard'));
    }
}
