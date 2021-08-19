<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Jwt\Generator;
use App\Models\User;
use Tests\TestCase;

class JwtGuardTest extends TestCase
{
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();

        $this->user = $user;
        $this->token = Generator::token($user);
    }

    public function testGuardTokenParse(): void
    {
        $this->getJson('/api/v1/user?token=string')
            ->assertUnauthorized();
    }

    public function testGuardTokenValidation(): void
    {
        $this->user->delete();

        $this->getJson("/api/v1/user?token={$this->token}")
            ->assertUnauthorized();
    }

    public function testGuardWithHeaderToken(): void
    {
        $response = $this->getJson('/api/v1/user', [
            'Authorization' => $this->token,
        ]);

        $response->assertOk();
    }

    public function testGuardWithQueryToken(): void
    {
        $this->getJson("/api/v1/user?token={$this->token}")
            ->assertOk();
    }

    public function testGuardWithJsonBodyToken(): void
    {
        $response = $this->json('GET', '/api/v1/user', [
            'token' => $this->token,
        ]);

        $response->assertOk();
    }
}
