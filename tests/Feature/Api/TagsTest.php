<?php

namespace Tests\Feature\Api;

use App\Http\Resources\Api\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Tests\TestCase;

class TagsTest extends TestCase
{
    public function testReturnsTagsList(): void
    {
        $tags = Tag::factory()->count(5)->create();

        $response = $this->getJson('/api/tags');

        $response->assertOk()
            ->assertExactJson([
                'tags' => $tags->pluck('name'),
            ]);
    }

    public function testReturnsEmptyTagsList(): void
    {
        $response = $this->getJson('/api/tags');

        $response->assertOk()
            ->assertExactJson(['tags' => []]);
    }

    public function testTagResource(): void
    {
        /** @var Tag $tag */
        $tag = Tag::factory()->create();

        $resource = new TagResource($tag);

        /** @var Request $request */
        $request = $this->mock(Request::class);

        $tagResource = $resource->toArray($request);

        $this->assertSame(['name' => $tag->name], $tagResource);
    }
}
