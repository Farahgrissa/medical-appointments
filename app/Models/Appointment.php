<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'slot_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status', // see App\Enums\AppointmentStatus
        'reason',
    ];

    // start_time / end_time are TIME columns: kept as "H:i:s" strings (see Slot)
    protected $casts = [
        'appointment_date' => 'date:Y-m-d',
        'status' => AppointmentStatus::class,
    ];

    protected $appends = ['has_started'];

    /**
     * Patient relationship
     */
    public function patient(): BelongsTo
    {
        // withTrashed : le nom d'un compte supprimé reste visible dans l'historique
        return $this->belongsTo(User::class, 'patient_id')->withTrashed()->withDefault();
    }

    /**
     * Doctor relationship
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id')->withTrashed()->withDefault();
    }

    /**
     * Slot relationship
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class)->withDefault();
    }

    /**
     * Status check helpers
     */
    public function isConfirmed(): bool
    {
        return $this->status === AppointmentStatus::Confirmed;
    }

    public function isCancelled(): bool
    {
        return $this->status === AppointmentStatus::Cancelled;
    }

    public function isPending(): bool
    {
        return $this->status === AppointmentStatus::Pending;
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->appointment_date->toDateString().' '.$this->start_time);
    }

    /**
     * Once started, an appointment can no longer be confirmed, moved or cancelled:
     * the doctor can only mark it as completed or no-show.
     */
    public function hasStarted(): bool
    {
        return $this->startsAt()->lte(now());
    }

    /**
     * Exposed to the front-end as "has_started" to show the right actions.
     */
    public function getHasStartedAttribute(): bool
    {
        return $this->appointment_date && $this->start_time && $this->hasStarted();
    }

    /**
     * Scopes
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        $now = now();

        return $query->where(fn (Builder $q) => $q
                ->whereDate('appointment_date', '>', $now->toDateString())
                ->orWhere(fn (Builder $q2) => $q2
                    ->whereDate('appointment_date', $now->toDateString())
                    ->where('start_time', '>', $now->format('H:i:s'))))
            ->orderBy('appointment_date')
            ->orderBy('start_time');
    }

    public function scopePast(Builder $query): Builder
    {
        $now = now();

        return $query->where(fn (Builder $q) => $q
                ->whereDate('appointment_date', '<', $now->toDateString())
                ->orWhere(fn (Builder $q2) => $q2
                    ->whereDate('appointment_date', $now->toDateString())
                    ->where('start_time', '<=', $now->format('H:i:s'))))
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time');
    }

    /**
     * Filters of the appointment lists:
     *  - period : "upcoming" | "past" (default: all, newest first)
     *  - status : one of AppointmentStatus
     *  - search : name of the other party ($searchRelation = "patient" or "doctor")
     */
    public function scopeFilter(Builder $query, array $filters, string $searchRelation): Builder
    {
        $status = AppointmentStatus::tryFrom($filters['status'] ?? '');

        return $query
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q
                ->whereHas($searchRelation, fn (Builder $q2) => $q2->where('name', 'like', "%{$search}%")))
            ->when(
                ($filters['period'] ?? null) === 'upcoming',
                fn (Builder $q) => $q->upcoming(),
                fn (Builder $q) => ($filters['period'] ?? null) === 'past'
                    ? $q->past()
                    : $q->orderByDesc('appointment_date')->orderByDesc('start_time'),
            );
    }

    /**
     * Appointments that still hold their slot (pending or confirmed)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', AppointmentStatus::open());
    }

    /**
     * Helper: duration in minutes
     */
    public function durationInMinutes(): ?int
    {
        return $this->start_time && $this->end_time
            ? (int) Carbon::parse($this->start_time)->diffInMinutes(Carbon::parse($this->end_time))
            : null;
    }
}
