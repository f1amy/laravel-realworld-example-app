<?php

namespace Tests\Feature\Api\Profile;

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
            ->deleteJson("/api/profiles/{$this->user->username}/follow");
        $response->assertOk()
            ->assertJsonPath('profile.following', false);

        $this->assertTrue($this->user->followers->doesntContain($follower));

        $this->actingAs($follower)
            ->deleteJson("/api/profiles/{$this->user->username}/follow")
            ->assertOk();
    }

    public function testUnfollowProfileWithoutAuth(): void
    {
        $this->deleteJson("/api/profiles/{$this->user->username}/follow")
            ->assertUnauthorized();
    }

    public function testUnfollowNonExistentProfile(): void
    {
        $this->actingAs($this->user)
            ->deleteJson('/api/profiles/non-existent/follow')
            ->assertNotFound();
    }
}
