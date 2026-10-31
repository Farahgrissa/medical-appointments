<?php

namespace App\Enums;

/**
 * Slot statuses (stored in the slots.status string column).
 */
enum SlotStatus: string
{
    case Available = 'available';
    case Booked = 'booked';
    case Cancelled = 'cancelled';
}
