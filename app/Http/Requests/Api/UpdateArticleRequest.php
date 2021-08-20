<?php

namespace App\Http\Requests\Api;

class UpdateArticleRequest extends BaseArticleRequest
{
    public function rules()
    {
        return array_merge_recursive(parent::rules(), [
            'title' => ['required_without_all:description,body'],
            'slug' => ['required_with:title'],
            'description' => ['required_without_all:title,body'],
            'body' => ['required_without_all:title,description'],
        ]);
    }
}
