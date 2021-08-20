<?php

namespace App\Http\Requests\Api\v1;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
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
        $input = $this->input();
        $title = Arr::get($input, 'article.title');

        if (is_string($title)) {
            Arr::set($input, 'article.slug', Str::slug($title));
        } else {
            Arr::forget($input, 'article.slug');
        }

        $this->merge($input);
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
            'article.title' => [
                'required_without_all:article.description,article.body',
                'string', 'max:255',
            ],
            'article.slug' => [
                'required_with:article.title',
                'string', 'max:255', $unique,
            ],
            'article.description' => [
                'required_without_all:article.title,article.body',
                'string', 'max:510',
            ],
            'article.body' => [
                'required_without_all:article.title,article.description',
                'string',
            ],
        ];
    }
}
