<?php

namespace Tests\Feature\Api\Article;

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
            ->getJson('/api/articles/feed');

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->where('articlesCount', 20)
                    ->count('articles', 20)
                    ->has('articles', fn (AssertableJson $items) =>
                        $items->each(fn (AssertableJson $item) =>
                            $item->where('tagList', [])
                                ->whereAllType([
                                    'slug' => 'string',
                                    'title' => 'string',
                                    'description' => 'string',
                                    'body' => 'string',
                                    'createdAt' => 'string',
                                    'updatedAt' => 'string',
                                ])
                                ->whereAll([
                                    'favorited' => false,
                                    'favoritesCount' => 0,
                                ])
                                ->has('author', fn (AssertableJson $subItem) =>
                                    $subItem->where('following', true)
                                        ->whereAllType([
                                            'username' => 'string',
                                            'bio' => 'string|null',
                                            'image' => 'string|null',
                                        ])
                                )
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
            ->getJson('/api/articles/feed?limit=25');

        $response->assertOk()
            ->assertJsonPath('articlesCount', 25)
            ->assertJsonCount(25, 'articles');
    }

    public function testArticleFeedOffset(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/articles/feed?offset=20');

        $response->assertOk()
            ->assertJsonPath('articlesCount', 10)
            ->assertJsonCount(10, 'articles');
    }

    /**
     * @dataProvider queryProvider
     * @param array<mixed> $data
     * @param string|array<string> $errors
     */
    public function testArticleFeedValidation(array $data, $errors): void
    {
        $response = $this->actingAs($this->user)
            ->json('GET', '/api/articles/feed', $data);

        $response->assertUnprocessable()
            ->assertInvalid($errors);
    }

    public function testArticleFeedWithoutAuth(): void
    {
        $this->getJson('/api/articles/feed')
            ->assertUnauthorized();
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function queryProvider(): array
    {
        $errors = ['limit', 'offset'];

        return [
            'not integer' => [['limit' => 'string', 'offset' => 0.123], $errors],
            'less than zero' => [['limit' => -123, 'offset' => -321], $errors],
        ];
    }
}
