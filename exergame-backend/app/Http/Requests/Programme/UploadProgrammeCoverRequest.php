<?php

namespace App\Http\Requests\Programme;

use Illuminate\Foundation\Http\FormRequest;

class UploadProgrammeCoverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Please select an image to upload.',
            'file.image' => 'The file must be an image.',
            'file.mimes' => 'Only jpeg, png, jpg, gif, webp images are allowed.',
            'file.max' => 'The image size must not exceed 2MB.',
        ];
    }
}
