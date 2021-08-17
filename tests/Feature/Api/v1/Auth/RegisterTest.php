<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Jwt\Parser;
use App\Jwt\Validator;
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

        $response = $this->postJson('/api/v1/users', [
            'user' => [
                'username' => $username,
                'email' => $email,
                'password' => $this->faker->password(8),
            ],
        ]);

        $response->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('user', fn (AssertableJson $item) =>
                    $item->where('username', $username)
                        ->where('email', $email)
                        ->where('bio', null)
                        ->where('image', null)
                        ->whereType('token', 'string')
                )
            );

        $token = Parser::parse($response['user']['token']);
        $this->assertTrue(Validator::validate($token));
    }

    /**
     * @dataProvider userProvider
     * @param array<mixed> $data
     * @param array<string> $errors
     */
    public function testRegisterUserValidation(array $data, array $errors): void
    {
        $response = $this->postJson('/api/v1/users', $data);

        $response->assertStatus(422)
            ->assertInvalid($errors);
    }

    public function testRegisterUserValidationUnique(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/users', [
            'user' => [
                'username' => $user->username,
                'email' => $user->email,
                'password' => $this->faker->password(8),
            ],
        ]);

        $response->assertStatus(422)
            ->assertInvalid([
                'user.username', 'user.email',
            ]);
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function userProvider(): array
    {
        $errors = ['user.username', 'user.email', 'user.password'];

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
            'bad username' => [['user' => ['username' => 'user n@me']], ['user.username']],
            'not email' => [['user' => ['email' => 'not an email']], ['user.email']],
            'small password' => [['user' => ['password' => 'small']], ['user.password']],
        ];
    }
}
