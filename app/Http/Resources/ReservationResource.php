<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Event;
use App\Models\User;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $event = Event::find($this->event_id);
        $user = User::find($this->user_id);
        $timestamp = $this->pivot ? $this->pivot->created_at : $this->created_at;

        return [
            'event_name' => $event ? $event->title : null,
            'user_name' => $user ? $user->name : null,
            'reserved_at' => $timestamp ? $timestamp->format('Y-m-d H:i:s') : null,
            // Additional information that could be useful
            'message' => 'Reservation successful',
            'status' => 'confirmed'
        ];
    }
}
