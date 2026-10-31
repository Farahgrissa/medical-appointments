<?php

namespace Tests\Feature;

use App\Enums\SlotStatus;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SlotManagementTest extends TestCase
{
    use RefreshDatabase;

    private function tomorrow(): string
    {
        return now()->addDay()->toDateString();
    }

    private function createSlot(User $doctor, string $start, string $end, array $attributes = []): Slot
    {
        return Slot::create([
            'doctor_id' => $doctor->id,
            'date' => $this->tomorrow(),
            'start_time' => $start,
            'end_time' => $end,
            'status' => SlotStatus::Available,
            'slot_type' => 'consultation',
            ...$attributes,
        ]);
    }

    private function postSlot(User $doctor, string $start, string $end, ?string $date = null)
    {
        return $this->actingAs($doctor)->post(route('doctor.storeSlot'), [
            'date' => $date ?? $this->tomorrow(),
            'start_time' => $start,
            'end_time' => $end,
            'slot_type' => 'consultation',
        ]);
    }

    public function test_doctor_sees_their_slots_page(): void
    {
        $doctor = User::factory()->doctor()->create();
        $this->createSlot($doctor, '09:00:00', '09:30:00');
        $this->createSlot(User::factory()->doctor()->create(), '09:00:00', '09:30:00');

        $this->actingAs($doctor)
            ->get(route('doctor.slots'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Doctor/Slots')
                ->has('slots', 1));
    }

    public function test_doctor_can_create_a_slot(): void
    {
        $doctor = User::factory()->doctor()->create();

        $this->postSlot($doctor, '09:00', '09:30')
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('doctor.slots'));

        $slot = Slot::sole();
        $this->assertSame($doctor->id, $slot->doctor_id);
        $this->assertSame('09:00:00', $slot->start_time);
        $this->assertSame('09:30:00', $slot->end_time);
        $this->assertSame(SlotStatus::Available, $slot->status);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $this->postSlot(User::factory()->doctor()->create(), '10:00', '09:00')
            ->assertSessionHasErrors('end_time');
    }

    public function test_slot_cannot_be_in_the_past(): void
    {
        $this->postSlot(User::factory()->doctor()->create(), '09:00', '09:30', now()->subDay()->toDateString())
            ->assertSessionHasErrors('date');
    }

    /**
     * Existing slot: 09:00 - 10:00.
     */
    public static function overlappingRanges(): array
    {
        return [
            'same range' => ['09:00', '10:00'],
            'starts inside' => ['09:30', '10:30'],
            'ends inside' => ['08:30', '09:30'],
            'contained' => ['09:15', '09:45'],
            'contains the existing slot' => ['08:00', '11:00'],
        ];
    }

    /**
     * @dataProvider overlappingRanges
     */
    public function test_overlapping_slots_are_refused(string $start, string $end): void
    {
        $doctor = User::factory()->doctor()->create();
        $this->createSlot($doctor, '09:00:00', '10:00:00');

        $this->postSlot($doctor, $start, $end)->assertSessionHasErrors('start_time');

        $this->assertSame(1, Slot::count());
    }

    public function test_adjacent_slots_are_allowed(): void
    {
        $doctor = User::factory()->doctor()->create();
        $this->createSlot($doctor, '09:00:00', '10:00:00');

        $this->postSlot($doctor, '10:00', '10:30')->assertSessionHasNoErrors();
        $this->postSlot($doctor, '08:30', '09:00')->assertSessionHasNoErrors();

        $this->assertSame(3, Slot::count());
    }

    public function test_another_doctors_slot_does_not_conflict(): void
    {
        $this->createSlot(User::factory()->doctor()->create(), '09:00:00', '10:00:00');

        $this->postSlot(User::factory()->doctor()->create(), '09:00', '10:00')->assertSessionHasNoErrors();
    }

    public function test_doctor_can_delete_a_free_slot(): void
    {
        $doctor = User::factory()->doctor()->create();
        $slot = $this->createSlot($doctor, '09:00:00', '09:30:00');

        $this->actingAs($doctor)
            ->delete(route('doctor.destroySlot', $slot))
            ->assertRedirect(route('doctor.slots'));

        $this->assertNull($slot->fresh());
    }

    public function test_doctor_cannot_delete_a_booked_slot(): void
    {
        $doctor = User::factory()->doctor()->create();
        $slot = $this->createSlot($doctor, '09:00:00', '09:30:00', ['status' => SlotStatus::Booked]);

        $this->actingAs($doctor)->delete(route('doctor.destroySlot', $slot))->assertForbidden();

        $this->assertNotNull($slot->fresh());
    }

    public function test_doctor_cannot_delete_another_doctors_slot(): void
    {
        $slot = $this->createSlot(User::factory()->doctor()->create(), '09:00:00', '09:30:00');

        $this->actingAs(User::factory()->doctor()->create())
            ->delete(route('doctor.destroySlot', $slot))
            ->assertForbidden();
    }

    public function test_patients_cannot_manage_slots(): void
    {
        $this->postSlot(User::factory()->patient()->create(), '09:00', '09:30')->assertForbidden();
    }
}
