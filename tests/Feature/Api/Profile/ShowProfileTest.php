<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Tests\TestCase;

class ShowProfileTest extends TestCase
{
    private User $profile;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();
        $this->profile = $user;
    }

    public function testShowProfileWithoutAuth(): void
    {
        /** @var User $profile */
        $profile = User::factory()->create();

        $response = $this->getJson("/api/profiles/{$profile->username}");

        $response->assertOk()
            ->assertExactJson([
                'profile' => [
                    'username' => $profile->username,
                    'bio' => $profile->bio,
                    'image' => $profile->image,
                ],
            ]);
    }

    public function testShowUnfollowedProfile(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson("/api/profiles/{$this->profile->username}");

        $response->assertOk()
            ->assertJsonPath('profile.following', false);
    }

    public function testShowFollowedProfile(): void
    {
        /** @var User $user */
        $user = User::factory()
            ->hasAttached($this->profile, [], 'authors')
            ->create();

        $response = $this->actingAs($user)
            ->getJson("/api/profiles/{$this->profile->username}");

        $response->assertOk()
            ->assertJsonPath('profile.following', true);
    }

    public function testShowNonExistentProfile(): void
    {
        $this->getJson('/api/profiles/non-existent')
            ->assertNotFound();
    }
}
