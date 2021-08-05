<?php

namespace Tests\Feature\Api\v1\Profile;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testShowProfileWithoutAuth(): void
    {

    }

    public function testShowFollowedProfile(): void
    {

    }

    public function testShowUnfollowedProfile(): void
    {

    }

    public function testShowNonExistentProfile(): void
    {
        $response = $this->getJson("/api/v1/profiles/non-existent");

        $response->assertNotFound();
    }
}
