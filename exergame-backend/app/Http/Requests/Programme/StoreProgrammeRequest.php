<?php

namespace App\Http\Requests\Programme;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammeRequest extends FormRequest
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
        return [
            'typeId' => 'required|exists:game_types,id',
            'name' => 'required|string|max:255',
            'synopsis' => 'nullable|string',
            'coverImage' => 'nullable|url',
            'intensityLevel' => 'required|in:low,medium,high,extreme',
            'sessionType' => 'required|in:single,group',
            'maxParticipants' => 'required|integer|min:1',
            'durationMinutes' => 'required|integer|min:1',
            'isActive' => 'boolean',
        ];
    }
}
