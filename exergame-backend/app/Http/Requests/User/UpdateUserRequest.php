<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->input('id');

        return [
            'name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            // 'username' => 'sometimes|string|min:6|max:255|unique:users,username,' . $userId,
            'phone' => 'required|string|regex:/^\+?[0-9]{7,15}$/',
            'role' => 'sometimes|in:user,trainer,admin,superadmin',
            'birthYear' => 'sometimes|integer|min:1901|max:'.date('Y'),
            'gender' => 'sometimes|in:male,female,other',
            'schemeId' => 'sometimes|exists:user_schemes,id',
            'isActive' => 'sometimes|boolean',
            // Emergency contact fields
            'emergencyContactName' => 'nullable|string|regex:/^[a-zA-Z\s]+$/',
            'emergencyContactNumber' => 'nullable|string|regex:/^\+?[0-9]{7,15}$/|different:phone',
            'emergencyRelationship' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Full name must contain only letters and spaces',
            // 'username.regex' => 'Login ID must be alphanumeric without spaces',
            // 'username.unique' => 'Login ID already exists',
            'phone.regex' => 'Phone number format is invalid',
            'birthYear.max' => 'Birth year cannot be in the future',
            'emergencyContactName.regex' => 'Emergency contact name must contain only letters and spaces',
            'emergencyContactNumber.different' => 'Emergency contact number must be different from phone number',
            'emergencyContactNumber.regex' => 'Emergency contact number format is invalid',
        ];
    }

    public static function validateDecryptedData(array $data, int $userId): array
    {
        $rules = (new static)->rules();
        // $rules['username'] = 'sometimes|string|min:6|max:255|unique:users,username,'.$userId;

        return validator($data, $rules)->validate();
    }
}
