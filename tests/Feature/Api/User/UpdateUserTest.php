<?php

namespace Tests\Feature\Api\User;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;
    }

    public function testUpdateUser(): void
    {
        $this->assertNotEquals($username = 'new.username', $this->user->username);
        $this->assertNotEquals($email = 'newEmail@example.com', $this->user->email);
        $this->assertNotEquals($bio = 'New bio information.', $this->user->bio);
        $this->assertNotEquals($image = 'https://example.com/image.png', $this->user->image);

        // update by one to check required_without_all rule
        $this->actingAs($this->user)
            ->putJson('/api/user', ['user' => ['username' => $username]])
            ->assertOk();
        $this->actingAs($this->user)
            ->putJson('/api/user', ['user' => ['email' => $email]])
            ->assertOk();
        $this->actingAs($this->user)
            ->putJson('/api/user', ['user' => ['bio' => $bio]]);
        $response = $this->actingAs($this->user)
            ->putJson('/api/user', ['user' => ['image' => $image]]);

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('user', fn (AssertableJson $item) =>
                    $item->whereType('token', 'string')
                        ->whereAll([
                            'username' => $username,
                            'email' => $email,
                            'bio' => $bio,
                            'image' => $image,
                        ])
                )
            );
    }

    /**
     * @dataProvider userProvider
     * @param array<mixed> $data
     * @param string|array<string> $errors
     */
    public function testUpdateUserValidation(array $data, $errors): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/user', $data);

        $response->assertUnprocessable()
            ->assertInvalid($errors);
    }

    public function testUpdateUserValidationUnique(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson('/api/user', [
                'user' => [
                    'username' => $anotherUser->username,
                    'email' => $anotherUser->email,
                ],
            ]);

        $response->assertUnprocessable()
            ->assertInvalid(['username', 'email']);
    }

    public function testSelfUpdateUserValidationUnique(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/user', [
                'user' => [
                    'username' => $this->user->username,
                    'email' => $this->user->email,
                ],
            ]);

        $response->assertOk();
    }

    public function testUpdateUserSetNull(): void
    {
        /** @var User $user */
        $user = User::factory()
            ->state([
                'bio' => 'not-null',
                'image' => 'https://example.com/image.png',
            ])
            ->create();

        $response = $this->actingAs($user)
            ->putJson('/api/user', [
                'user' => [
                    'bio' => null,
                    'image' => null,
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('user.bio', null)
            ->assertJsonPath('user.image', null);
    }

    public function testUpdateUserWithoutAuth(): void
    {
        $this->putJson('/api/user')
            ->assertUnauthorized();
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function userProvider(): array
    {
        $strErrors = ['username', 'email'];
        $allErrors = array_merge($strErrors, ['bio', 'image']);

        return [
            'required' => [[], 'any'],
            'wrong type' => [[
                'user' => [
                    'username' => 123,
                    'email' => null,
                    'bio' => [],
                    'image' => 123.0,
                ],
            ], $allErrors],
            'empty strings' => [[
                'user' => [
                    'username' => '',
                    'email' => '',
                ],
            ], $strErrors],
            'bad username' => [['user' => ['username' => 'user n@me']], 'username'],
            'not email' => [['user' => ['email' => 'not an email']], 'email'],
            'not url' => [['user' => ['image' => 'string']], 'image'],
        ];
    }
}
