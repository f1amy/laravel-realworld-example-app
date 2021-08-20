<?php

namespace App\Http\Requests\Api;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

abstract class BaseArticleRequest extends FormRequest
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
            'title' => ['string', 'max:255'],
            'slug' => ['string', 'max:255', $unique],
            'description' => ['string', 'max:510'],
            'body' => ['string'],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function validationData()
    {
        return Arr::wrap($this->input('article'));
    }
}
