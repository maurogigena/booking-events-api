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
        $timestamp = $this->pivot ? $this->pivot->created_at : $this->created_at;

        return [
            'event_title' => $this->event?->title,
            'user_name' => $this->user?->name,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $timestamp->format('Y-m-d H:i:s'),
        ];
    }
}
