<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email'
            ],

            'password' => ['required', 'min:8', 'confirmed'],

            'department' => [
                'required',
                Rule::exists('departments', 'name') // Must match an existing department
            ],

            'role' => [
                'nullable',
                'string',
                'max:255' // Let controller handle dynamic role creation
            ]
        ];
    }

    public function messages()
    {
        return [
            'department.exists' => 'The selected department is invalid.',
        ];
    }
}
