<?php

namespace Tests\Feature\Api\v1\User;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ShowUserTest extends TestCase
{
    use WithFaker;

    public function testShowUser(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/user');

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('user', fn (AssertableJson $item) =>
                    $item->where('username', $user->username)
                        ->where('email', $user->email)
                        ->where('bio', $user->bio)
                        ->where('image', $user->image)
                )
            );
    }

    public function testShowUserWithoutAuth(): void
    {
        $this->getJson('/api/v1/user')
            ->assertUnauthorized();
    }
}
