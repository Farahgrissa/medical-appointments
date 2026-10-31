<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_edit_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->patient()->create())
            ->get('/patient/profile/edit')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Profile/Edit_UpdateProfile'));
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->patient()->create();

        $this->actingAs($user)
            ->from('/patient/profile/edit')
            ->put('/patient/profile/update', [
                'name' => 'Test User',
                'phone' => '0600000000',
                'gender' => 'female',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/patient/profile/edit');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('0600000000', $user->phone);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_password_can_be_changed_with_current_password(): void
    {
        $user = User::factory()->patient()->create();

        $this->actingAs($user)
            ->put('/patient/profile/update', [
                'name' => $user->name,
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_password_cannot_be_changed_without_current_password(): void
    {
        $user = User::factory()->patient()->create();

        $this->actingAs($user)
            ->put('/patient/profile/update', [
                'name' => $user->name,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_password_cannot_be_changed_with_wrong_current_password(): void
    {
        $user = User::factory()->patient()->create();

        $this->actingAs($user)
            ->put('/patient/profile/update', [
                'name' => $user->name,
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->patient()->create();

        $this->actingAs($user)
            ->delete('/patient/profile/destroy')
            ->assertRedirect('/');

        $this->assertGuest();
        // Soft delete : le compte est conservé pour l'historique, mais ne peut plus se connecter
        $this->assertSoftDeleted($user);
    }
}
