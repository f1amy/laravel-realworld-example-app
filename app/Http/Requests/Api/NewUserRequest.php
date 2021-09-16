<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
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
            'username' => [
                'required', 'string', 'regex:' . User::REGEX_USERNAME,
                'max:255', 'unique:users,username'
            ],
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required', 'string', 'max:255',
                // we can set additional password requirements below
                Password::min(8),
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function validationData()
    {
        return Arr::wrap($this->input('user'));
    }
}
