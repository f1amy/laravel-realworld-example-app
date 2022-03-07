<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'author_id' => User::factory(),
            'slug' => fn (array $attrs) => Str::slug($attrs['title']),
            'title' => $this->faker->unique()->sentence(4),
            'description' => $this->faker->paragraph(),
            'body' => $this->faker->text(),
            'created_at' => function (array $attributes) {
                $user = User::find($attributes['author_id']);

                return $this->faker->dateTimeBetween($user->created_at);
            },
            'updated_at' => function (array $attributes) {
                $createdAt = $attributes['created_at'];

                return $this->faker->optional(25, $createdAt)
                    ->dateTimeBetween($createdAt);
            },
        ];
    }
}
