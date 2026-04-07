<?php

namespace App\Http\Requests\ProgrammeSchedule;

use Illuminate\Foundation\Http\FormRequest;

class GetMonthlySchedulesRequest extends FormRequest
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
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be an integer.',
            'year.min' => 'Year must be at least 2020.',
            'year.max' => 'Year cannot exceed 2050.',
            'month.required' => 'Month is required.',
            'month.integer' => 'Month must be an integer.',
            'month.min' => 'Month must be at least 1.',
            'month.max' => 'Month cannot exceed 12.',
        ];
    }
}
