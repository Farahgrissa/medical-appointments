<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'slot_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status', // scheduled, confirmed, cancelled, completed, no_show
        'reason',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date:Y-m-d',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
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
        return $this->status === 'confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Scopes
     */
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now()->startOfDay())
                     ->orderBy('appointment_date')
                     ->orderBy('start_time');
    }

    public function scopePast($query)
    {
        return $query->where('appointment_date', '<', now()->startOfDay())
                     ->orderByDesc('appointment_date')
                     ->orderByDesc('start_time');
    }

    /**
     * Helper: duration in minutes
     */
    public function durationInMinutes(): ?int
    {
        return $this->start_time && $this->end_time 
            ? $this->start_time->diffInMinutes($this->end_time)
            : null;
    }
}
