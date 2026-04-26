<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxDate = Carbon::today()->addDays((int) config('booking.max_advance_days', 30))->toDateString();
        $allowedSlots = (array) config('booking.slots', []);

        return [
            'service_slug' => ['required', 'string', 'max:255'],
            'pet_id' => ['nullable', 'string', 'max:255'],
            'new_pet_name' => ['nullable', 'string', 'max:255', 'required_without:pet_id'],
            'new_pet_breed' => ['nullable', 'string', 'max:255'],
            'new_pet_age' => ['nullable', 'integer', 'min:0', 'max:99'],
            'new_pet_notes' => ['nullable', 'string', 'max:2000'],
            'booking_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', "before_or_equal:{$maxDate}"],
            'start_time' => ['required', 'string', 'in:'.implode(',', $allowedSlots)],
            'taxi_enabled' => ['nullable', 'boolean'],
            'pickup_address' => ['nullable', 'string', 'max:500', 'required_if:taxi_enabled,1', 'required_if:taxi_enabled,true'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'taxi_enabled' => filter_var($this->input('taxi_enabled'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
