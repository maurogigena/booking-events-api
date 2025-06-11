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
        $timestamp = $this->pivot ? $this->pivot->reservation_deadline : $this->reservation_deadline;

        return [
            'title' => $this->title,
            'description' => $this->description,
            'date_time' => $this->date_time,
            'location' => $this->location,
            'price' => '$' . $this->price . ' USD',
            'reservation_deadline' => $timestamp->format('Y-m-d H:i:s'),
            'attendees_count' => $this->attendees_count ?? 0,
            'attendee_limit' => $this->attendee_limit,
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
