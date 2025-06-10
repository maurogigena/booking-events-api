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
        return match ($this->method()) {
            'POST' => $this->storeRules(),
            'PUT' => $this->replaceRules(),
            'PATCH' => $this->updateRules(),
            default => [],
        };
    }

    public function storeRules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ];
    }

    public function updateRules(): array
    {
        return [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|nullable|string',
        ];
        }

    public function replaceRules(): array
    {
        return $this->storeRules();
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
