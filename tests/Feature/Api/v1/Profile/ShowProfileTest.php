<?php

namespace Tests\Feature\Api\v1\Profile;

use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowProfileTest extends TestCase
{
    public function testShowProfileWithoutAuth(): void
    {
        Storage::fake('public');

        /** @var User $profile */
        $profile = User::factory()->withImage()->create();
        $image = $profile->image;

        $this->assertInstanceOf(File::class, $image);
        $this->assertFileExists($image->path());

        $response = $this->getJson("/api/v1/profiles/{$profile->username}");

        $response->assertOk()
            ->assertExactJson([
                'profile' => [
                    'username' => $profile->username,
                    'bio' => $profile->bio,
                    'image' => "/storage/images/{$image->getBasename()}",
                ],
            ]);
    }

    public function testShowUnfollowedProfile(): void
    {
        /** @var User $profile */
        $profile = User::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/v1/profiles/{$profile->username}");

        $response->assertOk()
            ->assertJsonPath('profile.following', false);
    }

    public function testShowFollowedProfile(): void
    {
        /** @var User $profile */
        $profile = User::factory()->create();
        /** @var User $user */
        $user = User::factory()
            ->hasAttached($profile, [], 'authors')
            ->create();

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/v1/profiles/{$profile->username}");

        $response->assertOk()
            ->assertJsonPath('profile.following', true);
    }

    public function testShowNonExistentProfile(): void
    {
        $response = $this->getJson('/api/v1/profiles/non-existent');

        $response->assertNotFound();
    }
}
