<?php

namespace App\Http\Requests\Api\v1;

class NewArticleRequest extends UpdateArticleRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'article.tagList' => 'sometimes|array',
            'article.tagList.*' => 'required|string|max:255',
        ]);
    }
}
