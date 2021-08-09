<?php

namespace Tests\Feature\Api\v1;

use App\Models\Tag;
use Tests\TestCase;

class TagsTest extends TestCase
{
    public function testReturnsTagsList(): void
    {
        $tags = Tag::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/tags');

        $response->assertOk()
            ->assertExactJson([
                'tags' => $tags->pluck('name'),
            ]);
    }

    public function testReturnsEmptyTagsList(): void
    {
        $response = $this->getJson('/api/v1/tags');

        $response->assertOk()
            ->assertExactJson(['tags' => []]);
    }
}
