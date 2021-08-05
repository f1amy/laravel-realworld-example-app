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

    }

    public function testFollowAlreadyFollowedProfile(): void
    {

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
            ->postJson("/api/v1/profiles/non-existent/follow");

        $response->assertNotFound();
    }
}
