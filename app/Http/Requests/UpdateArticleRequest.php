<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'article.slug' => Str::slug(
                (string) $this->input('article.title')
            ),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $article = Article::whereSlug($this->route('slug'))
            ->first();

        $unique = Rule::unique('articles', 'slug');
        if ($article !== null) {
            $unique->ignoreModel($article);
        }

        return [
            'article.title' => 'required|string',
            'article.slug' => ['required', 'string', $unique],
            'article.description' => 'required|string',
            'article.body' => 'required|string',
        ];
    }
}
