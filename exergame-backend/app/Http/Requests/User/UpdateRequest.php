<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            // 'username' => 'required_unless:id|string|min:6|max:255|unique:users,username',
            // 'password' => 'required_unless:id|string|min:8',
            // 'gender' => 'required_unless:id|in:male,female,other',
            // 'scheme' => 'required_unless:id|string',
            'phone' => 'required|string|regex:/^\+?[0-9]{7,15}$/',
            // 'birthYear' => 'required_unless:id|integer|min:1950|max:' . date('Y'),
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Full name must contain only letters and spaces',
            'phone.regex' => 'Phone number format is invalid',
        ];
    }

    public static function validateDecryptedData(array $data): array
    {
        return validator($data, (new static)->rules())->validate();
    }
}
