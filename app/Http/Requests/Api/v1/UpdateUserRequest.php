<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function rules()
    {
        /** @var \App\Models\User|null $user */
        $user = $this->user();

        if ($user === null) {
            throw new AuthenticationException();
        }

        return [
            'user.username' => [
                'required_without_all:user.email,user.bio,user.image',
                'alpha_num',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->getKey()),
            ],
            'user.email' => [
                'required_without_all:user.username,user.bio,user.image',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'user.bio' => 'required_without_all:user.username,user.email,user.image|string',
            'user.image' => 'required_without_all:user.username,user.email,user.bio|file|image',
        ];
    }
}
