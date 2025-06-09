<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Requests\ReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Traits\ApiResponses;

class ReservationController extends Controller
{
    use ApiResponses;

    public function reserve(ReservationRequest $request, Event $event)
    {
        $user = $request->user();

        // Create the reservation
        $event->attendees()->attach($user->id, [
            'created_at' => now()
        ]);
        
        // Get the reservation with needed relationships
        $reservation = $event->attendees()
            ->where('user_id', $user->id)
            ->with(['user', 'event'])
            ->first()
            ->pivot;

        return $this->success('Reservation created successfully', new ReservationResource($reservation));
    }

    public function cancel(Request $request, Event $event)
    {
        $user = $request->user();

        // Verify if the user has a reservation for this event
        if (!$event->attendees()->where('user_id', $user->id)->exists()) {
            return $this->error('You do not have a reservation for this event.', 404);
        }

        // Delete the reservation
        $event->attendees()->detach($user->id);

        return $this->success('Reservation cancelled successfully');
    }
}