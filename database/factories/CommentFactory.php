<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'article_id' => Article::factory(),
            'author_id' => User::factory(),
            'body' => $this->faker->sentence(),
            'created_at' => function (array $attributes) {
                $article = Article::find($attributes['article_id']);

                return $this->faker->dateTimeBetween($article->created_at);
            },
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }
}
