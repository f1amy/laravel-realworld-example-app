<?php

namespace Tests\Feature\Api\v1\User;

use App\Jwt\Parser;
use App\Jwt\Validator;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
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

    public function testShowUserWithoutAuth(): void
    {
        $this->getJson('/api/v1/user')
            ->assertUnauthorized();
    }
}
