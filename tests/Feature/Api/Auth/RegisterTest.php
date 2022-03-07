<?php

namespace Tests\Feature\Api\Auth;

use App\Jwt;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use WithFaker;

    public function testRegisterUser(): void
    {
        $username = $this->faker->userName();
        $email = $this->faker->safeEmail();

        $response = $this->postJson('/api/users', [
            'user' => [
                'username' => $username,
                'email' => $email,
                'password' => $this->faker->password(8),
            ],
        ]);

        $response->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('user', fn (AssertableJson $item) =>
                    $item->whereType('token', 'string')
                        ->whereAll([
                            'username' => $username,
                            'email' => $email,
                            'bio' => null,
                            'image' => null,
                        ])
                )
            );

        $token = Jwt\Parser::parse($response['user']['token']);
        $this->assertTrue(Jwt\Validator::validate($token));
    }

    /**
     * @dataProvider userProvider
     * @param array<mixed> $data
     * @param string|array<string> $errors
     */
    public function testRegisterUserValidation(array $data, $errors): void
    {
        $response = $this->postJson('/api/users', $data);

        $response->assertUnprocessable()
            ->assertInvalid($errors);
    }

    public function testRegisterUserValidationUnique(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->postJson('/api/users', [
            'user' => [
                'username' => $user->username,
                'email' => $user->email,
                'password' => $this->faker->password(8),
            ],
        ]);

        $response->assertUnprocessable()
            ->assertInvalid(['username', 'email']);
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function userProvider(): array
    {
        $errors = ['username', 'email', 'password'];

        return [
            'required' => [[], $errors],
            'not strings' => [[
                'user' => [
                    'username' => 123,
                    'email' => [],
                    'password' => null,
                ],
            ], $errors],
            'empty strings' => [[
                'user' => [
                    'username' => '',
                    'email' => '',
                    'password' => '',
                ],
            ], $errors],
            'bad username' => [['user' => ['username' => 'user n@me']], 'username'],
            'not email' => [['user' => ['email' => 'not an email']], 'email'],
            'small password' => [['user' => ['password' => 'small']], 'password'],
        ];
    }
}
