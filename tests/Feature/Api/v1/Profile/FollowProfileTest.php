<?php

namespace Tests\Feature\Api\v1\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testFollowProfile(): void
    {
        /** @var User $author */
        $author = User::factory()->create();
        /** @var User $follower */
        $follower = User::factory()->create();

        $this->assertFalse($author->followers->contains($follower));

        $response = $this->actingAs($follower, 'api')
            ->postJson("/api/v1/profiles/{$author->username}/follow");
        $response->assertOk()
            ->assertJsonPath('profile.following', true);

        $this->assertTrue($follower->authors->contains($author));

        $repeatedResponse = $this->actingAs($follower, 'api')
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

        $response = $this->actingAs($user, 'api')
            ->postJson('/api/v1/profiles/non-existent/follow');

        $response->assertNotFound();
    }
}
