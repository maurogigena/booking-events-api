<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Event;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [
            'event_id.required' => 'An event ID is required.',
            'event_id.exists' => 'The selected event does not exist.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $event = $this->route('event');
            
            if ($event->user_id === $this->user()->id) {
                $validator->errors()->add('event', 'You cannot make a reservation for your own event.');
                return;
            }
            
            if ($event->attendees()->count() >= $event->attendee_limit) {
                $validator->errors()->add('event', 'No available seats for this event.');
            }

            if (now()->gt($event->reservation_deadline)) {
                $validator->errors()->add('event', 'Reservation deadline has passed.');
            }

            if ($event->attendees()->where('user_id', $this->user()->id)->exists()) {
                $validator->errors()->add('event', 'You already have a reservation for this event.');
            }
        });
    }
} 