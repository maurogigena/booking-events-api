<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date_time' => 'required|date|after:now',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'attendee_limit' => 'required|integer|min:1',
            'reservation_deadline' => 'required|date|before:date_time',
        ];
    }

    public function updateRules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date_time' => 'sometimes|date|after:now',
            'location' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'attendee_limit' => 'sometimes|integer|min:1',
            'reservation_deadline' => 'sometimes|date|before:date_time',
        ];
    }

    public function replaceRules(): array
    {
        return $this->storeRules();
    }
}
