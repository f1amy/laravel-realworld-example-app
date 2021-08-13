<?php

namespace Tests\Feature\Api\v1\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    public function testUpdateUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->assertNotEquals($username = 'new.username', $user->username);
        $this->assertNotEquals($email = 'newEmail@example.com', $user->email);
        $this->assertNotEquals($bio = 'New bio information.', $user->bio);

        // update by one to check required_without_all rule
        $this->actingAs($user)
            ->putJson('/api/v1/user', ['user' => ['username' => $username]])
            ->assertOk();
        $this->actingAs($user)
            ->putJson('/api/v1/user', ['user' => ['email' => $email]])
            ->assertOk();
        $response = $this->actingAs($user)
            ->putJson('/api/v1/user', ['user' => ['bio' => $bio]]);

        $response->assertOk()
            ->assertExactJson([
                'user' => [
                    'username' => $username,
                    'email' => $email,
                    'bio' => $bio,
                    'image' => $user->image,
                ],
            ]);
    }

    public function testUpdateUserImage(): void
    {
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create();
        $image = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)
            ->putJson('/api/v1/user', [
                'user' => [
                    'image' => $image,
                ],
            ]);

        Storage::disk('public')
            ->assertExists($imagePath = "images/{$image->hashName()}");

        $response->assertOk()
            ->assertJsonPath('user.image', "/storage/{$imagePath}");
    }

    /**
     * @todo should cover isValid check and $relPath = false
     * @see \App\Http\Controllers\Api\v1\UserController::storeUploadedImage()
     */
    public function testUpdateUserInvalidImage(): void
    {
        $this->markTestIncomplete('todo invalid image');
    }

    /**
     * @dataProvider userProvider
     * @param array<mixed> $data
     * @param array<string>|string $errors
     */
    public function testUpdateUserValidation(array $data, $errors): void
    {
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/v1/user', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($errors);
    }

    public function testUpdateUserValidationUnique(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/v1/user', [
                'user' => [
                    'username' => $anotherUser->username,
                    'email' => $anotherUser->email,
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'user.username', 'user.email',
            ]);
    }

    public function testUpdateUserWithoutAuth(): void
    {
        $this->putJson('/api/v1/user')
            ->assertUnauthorized();
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function userProvider(): array
    {
        $strErrors = ['user.username', 'user.email', 'user.bio'];
        $allErrors = array_merge($strErrors, ['user.image']);

        return [
            'required' => [[], $allErrors],
            'wrong type' => [[
                'user' => [
                    'username' => 123,
                    'email' => [],
                    'bio' => null,
                    'image' => 'string',
                ],
            ], $allErrors],
            'empty strings' => [[
                'user' => [
                    'username' => '',
                    'email' => '',
                    'bio' => '',
                ],
            ], $strErrors],
            'bad username' => [['user' => ['username' => 'user n@me']], 'user.username'],
            'not email' => [['user' => ['email' => 'not an email']], 'user.email'],
            'file but not image' => [[
                'user' => [
                    'image' => UploadedFile::fake()
                        ->create('file.txt', 100, 'text/plain'),
                ],
            ], 'user.image'],
        ];
    }
}
