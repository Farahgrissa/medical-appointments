<?php
namespace App\Http\Controllers;

use App\Enums\SlotStatus;
use App\Http\Requests\StoreSlotRequest;
use App\Models\Slot;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * A doctor manages their own availability slots.
 */
class SlotController extends Controller
{
    public function index(Request $request)
    {
        $slots = $request->user()->slots()
            ->upcoming()
            ->where('status', '!=', SlotStatus::Cancelled)
            ->with(['appointments' => fn ($q) => $q->active()->with('patient:id,name')])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Doctor/Slots', [
            'slots' => $slots,
            'slotTypes' => Slot::TYPES,
        ]);
    }

    public function store(StoreSlotRequest $request)
    {
        $request->user()->slots()->create([
            ...$request->slotData(),
            'status' => SlotStatus::Available,
        ]);

        return redirect()->route('doctor.slots')->with('success', 'Slot created.');
    }

    public function destroy(Slot $slot)
    {
        $this->authorize('delete', $slot);

        // Un créneau qui a un historique (rendez-vous annulé) est retiré, pas supprimé :
        // les rendez-vous ne doivent jamais disparaître.
        if ($slot->appointments()->exists()) {
            $slot->update(['status' => SlotStatus::Cancelled]);
        } else {
            $slot->delete();
        }

        return redirect()->route('doctor.slots')->with('success', 'Slot deleted.');
    }
}
