<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // store() rules === put() rules
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ];
    
        if ($this->method() === 'PATCH') {
            $rules['rating'] = 'sometimes|integer|min:1|max:5';
        }

        return $rules;
    }

    public function createReview($eventId)
    {
        $validated = $this->validate($this->rules());
        
        return Review::create([
            'user_id' => $this->user()->id,
            'event_id' => $eventId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);
    }
}
