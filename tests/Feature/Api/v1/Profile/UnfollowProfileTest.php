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

        $this->actingAs($follower)
            ->deleteJson("/api/v1/profiles/{$author->username}/follow")
            ->assertOk();
    }

    public function testUnfollowProfileWithoutAuth(): void
    {
        /** @var User $profile */
        $profile = User::factory()->create();

        $this->deleteJson("/api/v1/profiles/{$profile->username}/follow")
            ->assertUnauthorized();
    }

    public function testUnfollowNonExistentProfile(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson('/api/v1/profiles/non-existent/follow')
            ->assertNotFound();
    }
}
