<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AddUserRequest extends FormRequest
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
            'name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'username' => 'required|string|regex:/^[a-zA-Z0-9]+$/|min:4|max:255|unique:users,username',
            'phone' => 'required|string|regex:/^\+?[0-9]{7,15}$/',
            'role' => 'required|in:user,trainer,admin,superadmin',
            'birthYear' => 'required|integer|min:1901|max:'.date('Y'),
            'gender' => 'required|in:male,female,other',
            'schemeId' => 'required|exists:user_schemes,id',
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
            'username.regex' => 'Login ID must be alphanumeric without spaces',
            'username.unique' => 'Login ID already exists',
            'phone.regex' => 'Phone number format is invalid',
            'birthYear.max' => 'Birth year cannot be in the future',
            'emergencyContactName.regex' => 'Emergency contact name must contain only letters and spaces',
            'emergencyContactNumber.different' => 'Emergency contact number must be different from phone number',
            'emergencyContactNumber.regex' => 'Emergency contact number format is invalid',
        ];
    }

    public static function validateDecryptedData(array $data): array
    {
        return validator($data, (new static)->rules())->validate();
    }
}
