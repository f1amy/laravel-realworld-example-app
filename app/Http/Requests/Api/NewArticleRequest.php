<?php

namespace App\Http\Requests\Api;

class NewArticleRequest extends BaseArticleRequest
{
    public function rules()
    {
        return array_merge_recursive(parent::rules(), [
            'title' => ['required'],
            'slug' => ['required'],
            'description' => ['required'],
            'body' => ['required'],
            'tagList' => 'sometimes|array',
            'tagList.*' => 'required|string|max:255',
        ]);
    }
}
