<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'event_title' => $this->event->title ?? null,
            'user_name' => $this->user->name ?? null,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at
        ];
    }
}
