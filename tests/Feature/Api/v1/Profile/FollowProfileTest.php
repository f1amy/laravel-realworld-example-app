<?php

namespace Tests\Feature\Api\v1\Profile;

use App\Models\User;
use Tests\TestCase;

class FollowProfileTest extends TestCase
{
    public function testFollowProfile(): void
    {
        /** @var User $author */
        $author = User::factory()->create();
        /** @var User $follower */
        $follower = User::factory()->create();

        $response = $this->actingAs($follower)
            ->postJson("/api/v1/profiles/{$author->username}/follow");
        $response->assertOk()
            ->assertJsonPath('profile.following', true);

        $this->assertTrue($author->followers->contains($follower));

        $repeatedResponse = $this->actingAs($follower)
            ->postJson("/api/v1/profiles/{$author->username}/follow");
        $repeatedResponse->assertOk();

        $this->assertDatabaseCount('author_follower', 1);
    }

    public function testFollowProfileWithoutAuth(): void
    {
        /** @var User $profile */
        $profile = User::factory()->create();

        $response = $this->postJson("/api/v1/profiles/{$profile->username}/follow");

        $response->assertUnauthorized();
    }

    public function testFollowNonExistentProfile(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/profiles/non-existent/follow');

        $response->assertNotFound();
    }
}
