<?php

namespace Tests\Feature\Api\Auth;

use App\Jwt;
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
        $this->token = Jwt\Generator::token($user);
    }

    public function testGuardTokenParse(): void
    {
        $this->getJson('/api/user?token=string')
            ->assertUnauthorized();
    }

    public function testGuardTokenValidation(): void
    {
        $this->user->delete();

        $this->getJson("/api/user?token={$this->token}")
            ->assertUnauthorized();
    }

    public function testGuardWithHeaderToken(): void
    {
        $response = $this->getJson('/api/user', [
            'Authorization' => "Token {$this->token}",
        ]);

        $response->assertOk();
    }

    public function testGuardWithQueryToken(): void
    {
        $this->getJson("/api/user?token={$this->token}")
            ->assertOk();
    }

    public function testGuardWithJsonBodyToken(): void
    {
        $response = $this->json('GET', '/api/user', [
            'token' => $this->token,
        ]);

        $response->assertOk();
    }
}
