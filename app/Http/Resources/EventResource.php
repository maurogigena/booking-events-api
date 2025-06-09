<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // this resource will be used at show() method in EventController
        return [
            'title' => $this->title,
            'description' => $this->description,
            'date_time' => $this->date_time,
            'location' => $this->location,
            'price' => '$' . $this->price . ' USD',
            'attendee_limit' => $this->attendee_limit,
            'reservation_deadline' => $this->reservation_deadline,
            'attendees_count' => $this->attendees_count ?? $this->attendees?->count() ?? 0,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
