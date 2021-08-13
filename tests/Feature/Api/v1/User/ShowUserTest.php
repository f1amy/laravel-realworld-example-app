<?php

namespace Tests\Feature\Api\v1\User;

use App\Models\User;
use Tests\TestCase;

class ShowUserTest extends TestCase
{
    public function testShowUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/user');

        $response->assertOk()
            ->assertExactJson([
                'user' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'bio' => $user->bio,
                    'image' => $user->image,
                ],
            ]);
    }

    public function testShowUserWithoutAuth(): void
    {
        $this->getJson('/api/v1/user')
            ->assertUnauthorized();
    }
}
