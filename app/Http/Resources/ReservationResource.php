<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'event_name' => $this->event->title,
            'user_name' => $this->user->name,
            'reserved_at' => $this->created_at->format('Y-m-d H:i:s'),
            'message' => 'Reservation successful',
            'status' => 'confirmed'
        ];
    }
}
