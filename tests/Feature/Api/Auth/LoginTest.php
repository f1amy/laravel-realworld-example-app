<?php

namespace Tests\Feature\Api\Auth;

use App\Jwt\Parser;
use App\Jwt\Validator;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use WithFaker;

    public function testLoginUser(): void
    {
        $password = $this->faker->password(8);
        /** @var User $user */
        $user = User::factory()
            ->state(['password' => Hash::make($password)])
            ->create();

        $response = $this->postJson('/api/users/login', [
            'user' => [
                'email' => $user->email,
                'password' => $password,
            ],
        ]);

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('user', fn (AssertableJson $item) =>
                    $item->whereType('token', 'string')
                        ->whereAll([
                            'username' => $user->username,
                            'email' => $user->email,
                            'bio' => $user->bio,
                            'image' => $user->image,
                        ])
                )
            );

        $token = Parser::parse($response['user']['token']);
        $this->assertTrue(Validator::validate($token));
    }

    public function testLoginUserFail(): void
    {
        $password = 'knownPassword';
        /** @var User $user */
        $user = User::factory()
            ->state(['password' => Hash::make($password)])
            ->create();

        $response = $this->postJson('/api/users/login', [
            'user' => [
                'email' => $user->email,
                'password' => 'differentPassword',
            ],
        ]);

        $response->assertStatus(422)
            ->assertInvalid(['user']);
    }

    /**
     * @dataProvider credentialsProvider
     * @param array<mixed> $data
     * @param array<string> $errors
     */
    public function testLoginUserValidation(array $data, array $errors): void
    {
        $response = $this->postJson('/api/users/login', $data);

        $response->assertStatus(422)
            ->assertInvalid($errors);
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function credentialsProvider(): array
    {
        $errors = ['email', 'password'];

        return [
            'required' => [[], $errors],
            'not strings' => [[
                'user' => [
                    'email' => [],
                    'password' => null,
                ],
            ], $errors],
            'empty strings' => [[
                'user' => [
                    'email' => '',
                    'password' => '',
                ],
            ], $errors],
            'not email' => [['user' => ['email' => 'not an email']], ['email']],
        ];
    }
}
