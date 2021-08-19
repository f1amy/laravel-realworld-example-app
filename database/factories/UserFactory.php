<?php

namespace Database\Factories;

use App\Exceptions\CannotWriteFileException;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'bio' => $this->faker->optional()->paragraph(),
            'image' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // "password"
            'created_at' => $createdAt = $this->faker->dateTimeThisDecade(),
            'updated_at' => $this->faker->optional(50, $createdAt)
                ->dateTimeBetween($createdAt),
        ];
    }

    /**
     * Create a fake image for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withImage()
    {
        return $this->state(function (array $attributes) {
            $tempImagePath = $this->faker->image();
            $relImagePath = Storage::disk('public')
                ->putFile('images', $tempImagePath);

            if ($relImagePath === false) {
                throw new CannotWriteFileException('Failed to place file on public disk.');
            }

            $fullImagePath = Storage::disk('public')->path($relImagePath);

            return [
                'image' => new File($fullImagePath),
            ];
        });
    }
}
