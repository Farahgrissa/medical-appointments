<?php

namespace App\Models;

use App\Enums\SlotStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Slot extends Model
{
    public const TYPES = ['consultation', 'follow-up', 'emergency'];

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'status', // see App\Enums\SlotStatus
        'slot_type', // see Slot::TYPES
    ];

    // start_time / end_time are TIME columns: kept as "H:i:s" strings on purpose
    // (a datetime cast would write a full date into them).
    protected $casts = [
        'date' => 'date:Y-m-d',
        'status' => SlotStatus::class,
    ];

    /**
     * Doctor relationship
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id')
            ->withDefault();
    }

    /**
     * Appointments relationship
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Check if slot is available
     */
    public function isAvailable(): bool
    {
        return $this->status === SlotStatus::Available;
    }

    /**
     * Has the slot already started?
     */
    public function isPast(): bool
    {
        return $this->startsAt()->isPast();
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->date->toDateString().' '.$this->start_time);
    }

    /**
     * Scope for slots that have not started yet
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        $now = now();

        return $query->where(function (Builder $q) use ($now) {
            $q->where('date', '>', $now->toDateString())
              ->orWhere(fn (Builder $q2) => $q2
                  ->where('date', $now->toDateString())
                  ->where('start_time', '>', $now->format('H:i:s')));
        });
    }

    /**
     * Scope for available slots that can still be booked
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', SlotStatus::Available)->upcoming();
    }

    /**
     * Scope for doctor's slots
     */
    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Calculate slot duration in minutes
     */
    public function duration(): int
    {
        return (int) Carbon::parse($this->start_time)->diffInMinutes(Carbon::parse($this->end_time));
    }

    /**
     * Does the doctor already have a (non-cancelled) slot overlapping [$start, $end[ on $date?
     * Two ranges overlap when each one starts before the other ends.
     */
    public static function existsConflict(int $doctorId, string $date, string $start, string $end, ?int $ignoreSlotId = null): bool
    {
        return self::where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->where('status', '!=', SlotStatus::Cancelled)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($ignoreSlotId, fn (Builder $q) => $q->whereKeyNot($ignoreSlotId))
            ->exists();
    }
}
