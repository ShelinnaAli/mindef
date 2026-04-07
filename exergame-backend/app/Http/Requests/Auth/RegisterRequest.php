<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'username' => 'required|string|regex:/^[a-zA-Z0-9]+$/|min:4|unique:users,username',
            'password' => [
                'required',
                'regex:/^\S+$/', // No spaces allowed
                Password::min(8)
                    ->letters()
                    ->numbers()
                    ->symbols()
            ],
            'phone' => 'required|string|regex:/^\+?[0-9]{7,15}$/',
            'birthYear' => 'required|integer|max:'.date('Y'),
            'schemeId' => 'required|numeric|exists:user_schemes,id',
            'gender' => 'required|in:male,female,other',
            'emergencyContactName' => 'nullable|string|regex:/^[a-zA-Z\s]+$/',
            'emergencyContactNumber' => 'nullable|string|regex:/^\+?[0-9]{7,15}$/|different:phone',
            'emergencyRelationship' => 'nullable|string|regex:/^[a-zA-Z\s]+$/',
            'emergencyIsAggreedConsent' => 'required|accepted',
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
            'emergencyIsAggreedConsent.accepted' => 'You must agree to the emergency contact consent',
        ];
    }

    public static function validateDecryptedData(array $data): array
    {
        return validator($data, (new static)->rules())->validate();
    }
}
