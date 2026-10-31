<?php

namespace App\Http\Requests;

use App\Models\Slot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Slot::class);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_type' => ['required', Rule::in(Slot::TYPES)],
        ];
    }

    /**
     * Checks that need the other fields to be valid first.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $start = $this->input('start_time').':00';
                $end = $this->input('end_time').':00';

                if (Carbon::parse($this->input('date').' '.$start)->isPast()) {
                    $validator->errors()->add('start_time', 'The slot must start in the future.');

                    return;
                }

                if (Slot::existsConflict($this->user()->id, $this->input('date'), $start, $end)) {
                    $validator->errors()->add('start_time', 'This slot overlaps one of your existing slots.');
                }
            },
        ];
    }

    /**
     * Validated data with times normalised to the "H:i:s" format of the TIME columns.
     */
    public function slotData(): array
    {
        return [
            'date' => $this->validated('date'),
            'start_time' => $this->validated('start_time').':00',
            'end_time' => $this->validated('end_time').':00',
            'slot_type' => $this->validated('slot_type'),
        ];
    }
}
