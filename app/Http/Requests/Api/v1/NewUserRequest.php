<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class NewUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user.username' => [
                'required', 'string', 'regex:/^[\pL\pM\pN._-]+$/u',
                'max:255', 'unique:users,username'
            ],
            'user.email' => 'required|string|email|max:255|unique:users,email',
            'user.password' => [
                'required', 'string', 'max:255',
                // we can set additional password requirements below
                Password::min(8),
            ],
        ];
    }
}
