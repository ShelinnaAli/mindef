<?php

namespace App\Http\Requests\Programme;

use App\Models\Programme;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProgrammeRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'typeId' => 'sometimes|integer|exists:game_types,id',
            'synopsis' => 'nullable|string',
            'coverImage' => 'nullable|url',
            'intensityLevel' => 'sometimes|in:low,medium,high,extreme',
            'sessionType' => 'sometimes|in:single,group',
            'maxParticipants' => 'sometimes|integer|min:1',
            'durationMinutes' => 'nullable|integer|min:1',
            'isActive' => [
                'sometimes',
                'boolean',
                function ($attribute, $value, $fail) {
                    if ($value === false) {
                        $programmeId = $this->route('id');
                        $programme = Programme::find($programmeId);
                        if ($programme && $programme->is_active) {
                            $hasActiveSchedule = $programme->schedules()
                                ->where('day', '>=', date('Y-m-d'))
                                ->where('start_time', '>=', date('h:i:s'))
                                ->where('is_cancelled', false)
                                ->exists();
                            if ($hasActiveSchedule) {
                                $fail('Cannot deactivate programme with active schedules.');
                            }
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    /**
     * Get validation rules for the route parameter.
     */
    public function validationData()
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
        ]);
    }

    /**
     * Get custom validation rules that include route parameters.
     */
    public function getAllValidationRules(): array
    {
        return array_merge($this->rules(), [
            'id' => 'required|integer|exists:programmes,id',
        ]);
    }
}
