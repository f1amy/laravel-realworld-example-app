<?php

namespace Tests\Feature\Api\v1\Profile;

use App\Models\User;
use Tests\TestCase;

class UnfollowProfileTest extends TestCase
{
    public function testUnfollowProfile(): void
    {
        /** @var User $author */
        $author = User::factory()->create();
        /** @var User $follower */
        $follower = User::factory()
            ->hasAttached($author, [], 'authors')
            ->create();

        $response = $this->actingAs($follower)
            ->deleteJson("/api/v1/profiles/{$author->username}/follow");
        $response->assertOk()
            ->assertJsonPath('profile.following', false);

        $this->assertFalse($author->followers->contains($follower));

        $repeatedResponse = $this->actingAs($follower)
            ->deleteJson("/api/v1/profiles/{$author->username}/follow");
        $repeatedResponse->assertOk();
    }

    public function testUnfollowProfileWithoutAuth(): void
    {
        /** @var User $profile */
        $profile = User::factory()->create();

        $response = $this->deleteJson("/api/v1/profiles/{$profile->username}/follow");

        $response->assertUnauthorized();
    }

    public function testUnfollowNonExistentProfile(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/v1/profiles/non-existent/follow');

        $response->assertNotFound();
    }
}
