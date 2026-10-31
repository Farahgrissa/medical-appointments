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

    /**
     * Patient relationship
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id')->withDefault();
    }

    /**
     * Doctor relationship
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id')->withDefault();
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

    /**
     * Scopes
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', '>=', now()->toDateString())
                     ->orderBy('appointment_date')
                     ->orderBy('start_time');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereDate('appointment_date', '<', now()->toDateString())
                     ->orderByDesc('appointment_date')
                     ->orderByDesc('start_time');
    }

    /**
     * Appointments that still hold their slot (pending or confirmed)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '!=', AppointmentStatus::Cancelled);
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
