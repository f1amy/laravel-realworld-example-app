<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FeedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'limit' => 'sometimes|integer|min:0',
            'offset' => 'sometimes|integer|min:0',
        ];
    }
}
