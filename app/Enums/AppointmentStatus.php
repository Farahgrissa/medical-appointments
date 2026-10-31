<?php

namespace App\Enums;

/**
 * Appointment statuses (must match the enum column of the appointments table).
 *
 * pending -> confirmed -> completed | no_show
 *    \__________\______> cancelled
 */
enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
            self::NoShow => 'No show',
        };
    }

    /**
     * Statuses of an appointment that still holds its slot and can still change.
     *
     * @return list<self>
     */
    public static function open(): array
    {
        return [self::Pending, self::Confirmed];
    }

    public function isOpen(): bool
    {
        return in_array($this, self::open(), true);
    }
}
