<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $unique = Rule::unique('articles', 'title');

        $article = Article::whereSlug($this->route('slug'))->first();
        if ($article !== null) {
            $unique->ignoreModel($article);
        }

        return [
            'article.title' => ['required', 'string', $unique],
            'article.description' => 'required|string',
            'article.body' => 'required|string',
        ];
    }
}
