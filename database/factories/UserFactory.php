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
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'bio' => $this->faker->paragraph(),
            'image' => null,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
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

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
