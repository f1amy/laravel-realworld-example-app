<?php

namespace App\Http\Requests\Api;

class ArticleListRequest extends FeedRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'tag' => 'sometimes|string',
            'author' => 'sometimes|string',
            'favorited' => 'sometimes|string',
        ]);
    }
}
