<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // this resource will be used at index() method in EventController
        return [
            'id' => $this->id,
            'title' => $this->title,
            'attendee_limit' => $this->attendee_limit,
            'reservation_deadline' => $this->reservation_deadline,
            'price' => '$' . $this->price . ' USD',
            'attendees_count' => $this->attendees_count ?? 0,
            'created_at' => $this->created_at
        ];
    }
}
