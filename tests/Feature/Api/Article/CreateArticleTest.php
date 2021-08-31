<?php

namespace Tests\Feature\Api\Article;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateArticleTest extends TestCase
{
    use WithFaker;

    public function testCreateArticle(): void
    {
        /** @var User $author */
        $author = User::factory()->create();

        $title = 'Original title';
        $description = $this->faker->paragraph();
        $body = $this->faker->text();
        $tags = ['one', 'two', 'three', 'four', 'five'];

        $response = $this->actingAs($author)
            ->postJson('/api/articles', [
                'article' => [
                    'title' => $title,
                    'slug' => 'different-slug', // must be overwritten with title slug
                    'description' => $description,
                    'body' => $body,
                    'tagList' => $tags,
                ],
            ]);

        $response->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('article', fn (AssertableJson $item) =>
                    $item->where('tagList', $tags)
                        ->whereAll([
                            'slug' => 'original-title',
                            'title' => $title,
                            'description' => $description,
                            'body' => $body,
                            'favorited' => false,
                            'favoritesCount' => 0,
                        ])
                        ->whereAllType([
                            'createdAt' => 'string',
                            'updatedAt' => 'string',
                        ])
                        ->has('author', fn (AssertableJson $subItem) =>
                            $subItem->whereAll([
                                'username' => $author->username,
                                'bio' => $author->bio,
                                'image' => $author->image,
                                'following' => false,
                            ])
                        )
                )
            );
    }

    public function testCreateArticleEmptyTags(): void
    {
        /** @var User $author */
        $author = User::factory()->create();

        $response = $this->actingAs($author)
            ->postJson('/api/articles', [
                'article' => [
                    'title' => $this->faker->sentence(4),
                    'description' => $this->faker->paragraph(),
                    'body' => $this->faker->text(),
                    'tagList' => [],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('article.tagList', []);
    }

    public function testCreateArticleExistingTags(): void
    {
        /** @var User $author */
        $author = User::factory()->create();
        /** @var Tag[]|\Illuminate\Database\Eloquent\Collection $tags */
        $tags = Tag::factory()
            ->count(5)
            ->create();
        $tagsList = $tags->pluck('name')->toArray();

        $response = $this->actingAs($author)
            ->postJson('/api/articles', [
                'article' => [
                    'title' => $this->faker->sentence(4),
                    'description' => $this->faker->paragraph(),
                    'body' => $this->faker->text(),
                    'tagList' => $tagsList,
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('article.tagList', $tagsList);

        $this->assertDatabaseCount('tags', 5);
        $this->assertDatabaseCount('article_tag', 5);
    }

    /**
     * @dataProvider articleProvider
     * @param array<mixed> $data
     * @param string|array<string> $errors
     */
    public function testCreateArticleValidation(array $data, $errors): void
    {
        /** @var User $author */
        $author = User::factory()->create();

        $response = $this->actingAs($author)
            ->postJson('/api/articles', $data);

        $response->assertUnprocessable()
            ->assertInvalid($errors);
    }

    public function testCreateArticleValidationUnique(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->actingAs($article->author)
            ->postJson('/api/articles', [
                'article' => [
                    'title' => $article->title,
                    'description' => $this->faker->paragraph(),
                    'body' => $this->faker->text(),
                ],
            ]);

        $response->assertUnprocessable()
            ->assertInvalid('slug');
    }

    public function testCreateArticleWithoutAuth(): void
    {
        $response = $this->postJson('/api/articles', [
            'article' => [
                'title' => $this->faker->sentence(4),
                'description' => $this->faker->paragraph(),
                'body' => $this->faker->text(),
            ],
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function articleProvider(): array
    {
        $errors = ['title', 'description', 'body'];
        $tags = ['tagList.0', 'tagList.1', 'tagList.2'];

        return [
            'required' => [[], $errors],
            'not strings' => [[
                'article' => [
                    'title' => 123,
                    'description' => [],
                    'body' => null,
                    'tagList' => [
                        123, [], null,
                    ],
                ],
            ], array_merge($errors, $tags)],
            'not array' => [['article' => ['tagList' => 'str']], 'tagList'],
        ];
    }
}
