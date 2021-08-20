<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class NewCommentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'body' => 'required|string',
        ];
    }

    /**
     * @return array<mixed>
     */
    public function validationData()
    {
        return Arr::wrap($this->input('comment'));
    }
}
