<?php

namespace Tests\Feature\Api\v1\Profile;

use App\Models\User;
use Tests\TestCase;

class UnfollowProfileTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;
    }

    public function testUnfollowProfile(): void
    {
        /** @var User $follower */
        $follower = User::factory()
            ->hasAttached($this->user, [], 'authors')
            ->create();

        $response = $this->actingAs($follower)
            ->deleteJson("/api/v1/profiles/{$this->user->username}/follow");
        $response->assertOk()
            ->assertJsonPath('profile.following', false);

        $this->assertFalse($this->user->followers->contains($follower));

        $this->actingAs($follower)
            ->deleteJson("/api/v1/profiles/{$this->user->username}/follow")
            ->assertOk();
    }

    public function testUnfollowProfileWithoutAuth(): void
    {
        $this->deleteJson("/api/v1/profiles/{$this->user->username}/follow")
            ->assertUnauthorized();
    }

    public function testUnfollowNonExistentProfile(): void
    {
        $this->actingAs($this->user)
            ->deleteJson('/api/v1/profiles/non-existent/follow')
            ->assertNotFound();
    }
}
