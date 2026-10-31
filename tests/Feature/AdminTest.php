<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\SlotStatus;
use App\Models\Appointment;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    private function appointment(User $doctor, User $patient, AppointmentStatus $status, string $date): Appointment
    {
        $slot = Slot::create([
            'doctor_id' => $doctor->id,
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => SlotStatus::Booked,
        ]);

        return Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'slot_id' => $slot->id,
            'appointment_date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => $status,
        ]);
    }

    // ===== Statistiques =====

    public function test_dashboard_shows_statistics(): void
    {
        $doctor = User::factory()->doctor()->create();
        $patient = User::factory()->patient()->create();
        $past = now()->subDays(3)->toDateString();
        $future = now()->addDays(3)->toDateString();

        $this->appointment($doctor, $patient, AppointmentStatus::Completed, $past);
        $this->appointment($doctor, $patient, AppointmentStatus::NoShow, now()->subDays(2)->toDateString());
        $this->appointment($doctor, $patient, AppointmentStatus::Cancelled, now()->subDay()->toDateString());
        $this->appointment($doctor, $patient, AppointmentStatus::Confirmed, $future);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboards/AdminDashboard')
                ->where('stats.patients', 1)
                ->where('stats.doctors', 1)
                ->where('stats.appointments', 4)
                ->where('stats.upcomingAppointments', 1)
                ->where('stats.cancellationRate', 25) // 1 / 4
                ->where('stats.noShowRate', 50) // 1 no-show / 2 closed
                ->has('stats.byStatus', 5)
                ->where('stats.byStatus', fn ($rows) => collect($rows)->firstWhere('status', 'completed')['total'] === 1)
                ->has('latestUsers'));
    }

    public function test_rates_are_null_without_appointments(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('stats.cancellationRate', null)
                ->where('stats.noShowRate', null));
    }

    public function test_non_admins_cannot_access_the_admin_space(): void
    {
        $doctor = User::factory()->doctor()->create();

        $this->actingAs($doctor)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($doctor)->get(route('admin.users'))->assertForbidden();
        $this->actingAs($doctor)->post(route('admin.storeDoctor'), [])->assertForbidden();
    }

    // ===== Création de médecins =====

    public function test_admin_can_create_a_doctor_with_a_specialty(): void
    {
        Notification::fake();

        $this->actingAs($this->admin)
            ->get(route('admin.createDoctor'))
            ->assertInertia(fn (Assert $page) => $page->component('Admin/CreateDoctor'));

        $this->actingAs($this->admin)
            ->post(route('admin.storeDoctor'), [
                'name' => 'Dr. House',
                'email' => 'house@clinic.test',
                'specialty' => 'Diagnostics',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users', ['role' => 'doctor']));

        $doctor = User::where('email', 'house@clinic.test')->sole();
        $this->assertTrue($doctor->hasRole('doctor'));
        $this->assertSame('Diagnostics', $doctor->specialty);
        $this->assertTrue($doctor->hasVerifiedEmail());
        // Le médecin choisit son mot de passe via le lien de réinitialisation
        Notification::assertSentTo($doctor, ResetPassword::class);
    }

    public function test_doctor_creation_is_validated(): void
    {
        $deleted = User::factory()->patient()->create(['email' => 'taken@clinic.test']);
        $deleted->delete();

        $this->actingAs($this->admin)
            ->post(route('admin.storeDoctor'), [
                'name' => 'Dr. Nobody',
                'email' => 'taken@clinic.test', // pris par un compte supprimé (soft delete)
                'specialty' => '',
            ])
            ->assertSessionHasErrors(['email', 'specialty']);
    }

    // ===== Modération =====

    public function test_users_list_can_be_filtered(): void
    {
        User::factory()->doctor()->create(['name' => 'Dr. Active']);
        User::factory()->doctor()->deactivated()->create(['name' => 'Dr. Inactive']);
        User::factory()->patient()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.users', ['role' => 'doctor', 'state' => 'deactivated']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users')
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Dr. Inactive'));
    }

    public function test_deactivated_doctor_cannot_log_in_and_cannot_be_booked(): void
    {
        $doctor = User::factory()->doctor()->create();
        $slot = Slot::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => SlotStatus::Available,
        ]);
        $patient = User::factory()->patient()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.deactivateUser', $doctor))
            ->assertRedirect();
        $this->assertTrue($doctor->fresh()->isDeactivated());

        auth()->logout();

        // Connexion refusée
        $this->post('/login', ['email' => $doctor->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        // Invisible pour les patients, et non réservable
        $this->actingAs($patient)
            ->get(route('patient.doctors'))
            ->assertInertia(fn (Assert $page) => $page->has('doctors', 0));
        $this->actingAs($patient)->get(route('patient.doctorSlots', $doctor))->assertNotFound();
        $this->actingAs($patient)
            ->post(route('patient.bookAppointment'), ['slot_id' => $slot->id])
            ->assertSessionHasErrors('slot_id');
    }

    public function test_a_logged_in_user_is_logged_out_once_deactivated(): void
    {
        $patient = User::factory()->patient()->deactivated()->create();

        $this->actingAs($patient)
            ->get(route('patient.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_can_reactivate_an_account(): void
    {
        $doctor = User::factory()->doctor()->deactivated()->create();

        $this->actingAs($this->admin)->patch(route('admin.reactivateUser', $doctor))->assertRedirect();

        $this->assertFalse($doctor->fresh()->isDeactivated());
        $this->post('/logout');
        $this->post('/login', ['email' => $doctor->email, 'password' => 'password'])
            ->assertRedirect(route('doctor.dashboard'));
    }

    public function test_admin_accounts_cannot_be_deactivated(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($this->admin)->patch(route('admin.deactivateUser', $otherAdmin))->assertForbidden();
        $this->actingAs($this->admin)->patch(route('admin.deactivateUser', $this->admin))->assertForbidden();
    }
}
