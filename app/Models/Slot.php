<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'status', // available, booked, cancelled
        'slot_type', // consultation, follow-up, emergency
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
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
        return $this->status === 'available';
    }

    /**
     * Scope for available slots
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->where('date', '>=', now()->toDateString());
    }

    /**
     * Scope for doctor's slots
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Calculate slot duration in minutes
     */
    public function duration(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }
    public static function existsConflict($doctorId, $date, $start, $end)
{
    return self::where('doctor_id', $doctorId)
              ->where('date', $date)
              ->where(function($q) use ($start, $end) {
                  $q->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end]);
              })
              ->exists();
}
}