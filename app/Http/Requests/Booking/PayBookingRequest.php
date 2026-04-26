<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class PayBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $codes = [];
        foreach ((array) config('booking.payment_channels', []) as $group) {
            foreach ((array) $group as $channel) {
                if (isset($channel['code'])) {
                    $codes[] = (string) $channel['code'];
                }
            }
        }

        return [
            'payment_channel' => ['required', 'string', 'in:'.implode(',', $codes)],
        ];
    }
}
