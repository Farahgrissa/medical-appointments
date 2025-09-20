<?php
namespace App\Http\Controllers;

use App\Models\Slot;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function index($doctorId)
    {
        $slots = Slot::where('doctor_id', $doctorId)
                     ->where('status', 'available')
                     ->where('date', '>=', now()->toDateString())
                     ->orderBy('date')
                     ->orderBy('start_time')
                     ->get();

        return inertia('Slots/Index', [
            'slots' => $slots,
            'doctor_id' => $doctorId
        ]);
    }
}
