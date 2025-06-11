<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Requests\ReservationRequest;
use App\Http\Resources\ReservationResource;
use Illuminate\Http\JsonResponse;

class ReservationController extends Controller
{
    public function reserve(ReservationRequest $request, Event $event)
    {
        $user = $request->user();

        // Create the reservation
        $event->attendees()->attach($user->id, [
            'created_at' => now()
        ]);

        // Prepare data for the resource
        $reservationData = [
            'event' => $event,
            'user' => $user,
            'created_at' => now()
        ];

        return response()->json([
            'data' => new ReservationResource((object)$reservationData)
        ], 201);
    }

    public function cancel(Request $request, Event $event)
    {
        $user = $request->user();

        // Verify if the user has a reservation for this event
        if (!$event->attendees()->where('user_id', $user->id)->exists()) {
            abort(404, 'Reservation not found');
        }

        // Delete the reservation
        $event->attendees()->detach($user->id);

        return response()->json(200);
    }
}