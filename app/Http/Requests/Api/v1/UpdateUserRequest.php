<?php

namespace App\Http\Requests\Api\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        /** @var \App\Models\User|null $user */
        $user = $this->user();

        if ($user === null) {
            throw new InvalidArgumentException('User not authenticated.');
        }

        return [
            'user.username' => [
                'required_without_all:user.email,user.bio,user.image',
                'string', 'regex:/^[\pL\pM\pN._-]+$/u', 'max:255',
                Rule::unique('users', 'username')->ignore($user->getKey()),
            ],
            'user.email' => [
                'required_without_all:user.username,user.bio,user.image',
                'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'user.bio' => 'required_without_all:user.username,user.email,user.image|string',
            'user.image' => 'required_without_all:user.username,user.email,user.bio|file|image',
        ];
    }
}
