<?php

namespace Tests\Feature\Api\v1\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ArticleFeedTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // create 30 articles
        $authors = User::factory()
            ->has(Article::factory()->count(6), 'articles')
            ->count(5);

        /** @var User $user */
        $user = User::factory()
            ->has($authors, 'authors')
            ->create();

        $this->user = $user;
    }

    public function testArticleFeed(): void
    {
        // new dummy articles shouldn't be returned
        Article::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/articles/feed');

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('articlesCount', 20)
                    ->has('articles', 20, fn (AssertableJson $item) =>
                        $item->whereAllType([
                                'slug' => 'string',
                                'title' => 'string',
                                'description' => 'string',
                                'body' => 'string',
                                'createdAt' => 'string',
                                'updatedAt' => 'string',
                            ])
                            ->where('tagList', [])
                            ->where('favorited', false)
                            ->where('favoritesCount', 0)
                            ->has('author', fn (AssertableJson $subItem) =>
                                $subItem->whereAllType([
                                        'username' => 'string',
                                        'bio' => 'string',
                                        'image' => 'string|null',
                                    ])
                                    ->where('following', true)
                            )
                    )
            );

        // verify all authors are followed
        foreach ($response['articles'] as $article) {
            $this->assertTrue(
                Arr::get($article, 'author.following'),
                'Author of the article must be followed.'
            );
        }
    }

    public function testArticleFeedLimit(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/articles/feed?limit=25');

        $response->assertOk()
            ->assertJsonPath('articlesCount', 25)
            ->assertJsonCount(25, 'articles');
    }

    public function testArticleFeedOffset(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/articles/feed?offset=15');

        $response->assertOk()
            ->assertJsonPath('articlesCount', 15)
            ->assertJsonCount(15, 'articles');
    }

    /**
     * @dataProvider feedProvider
     * @param array<mixed> $data
     * @param array<string> $errors
     */
    public function testArticleFeedValidation(array $data, array $errors): void
    {
        $response = $this->actingAs($this->user)
            ->json('GET', '/api/v1/articles/feed', $data);

        $response->assertStatus(422)
            ->assertInvalid($errors);
    }

    public function testArticleFeedWithoutAuth(): void
    {
        $this->getJson('/api/v1/articles/feed')
            ->assertUnauthorized();
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function feedProvider(): array
    {
        $errors = ['limit', 'offset'];

        return [
            'not integer' => [[
                'limit' => 'string',
                'offset' => 0.123,
            ], $errors],
            'less than zero' => [[
                'limit' => -123,
                'offset' => -321,
            ], $errors],
        ];
    }
}
