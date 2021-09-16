<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
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
            'username' => [
                'sometimes', 'string', 'max:255', 'regex:' . User::REGEX_USERNAME,
                Rule::unique('users', 'username')
                    ->ignore($user->getKey()),
            ],
            'email' => [
                'sometimes', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->getKey()),
            ],
            'bio' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|string|url',
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
