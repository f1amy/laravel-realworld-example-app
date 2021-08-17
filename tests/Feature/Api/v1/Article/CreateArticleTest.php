<?php

namespace Tests\Feature\Api\v1\Article;

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
        $tags = $this->faker->unique()->words(5);

        $response = $this->actingAs($author)
            ->postJson('/api/v1/articles', [
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
                    $item->where('slug', 'original-title')
                        ->where('title', $title)
                        ->where('description', $description)
                        ->where('body', $body)
                        ->whereType('tagList', 'array')
                        ->has('tagList', 5)
                        ->whereContains('tagList', $tags)
                        ->whereAllType([
                            'createdAt' => 'string',
                            'updatedAt' => 'string',
                        ])
                        ->where('favorited', false)
                        ->where('favoritesCount', 0)
                        ->has('author', fn (AssertableJson $subItem) =>
                            $subItem->where('username', $author->username)
                                ->where('bio', $author->bio)
                                ->where('image', $author->image)
                                ->where('following', false)
                        )
                )
            );
    }

    public function testCreateArticleEmptyTags(): void
    {
        /** @var User $author */
        $author = User::factory()->create();

        $response = $this->actingAs($author)
            ->postJson('/api/v1/articles', [
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
            ->postJson('/api/v1/articles', [
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
     * @param array<string> $errors
     */
    public function testCreateArticleValidation(array $data, array $errors): void
    {
        /** @var User $author */
        $author = User::factory()->create();

        $response = $this->actingAs($author)
            ->postJson('/api/v1/articles', $data);

        $response->assertStatus(422)
            ->assertInvalid($errors);
    }

    public function testCreateArticleValidationUnique(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->actingAs($article->author)
            ->postJson('/api/v1/articles', [
                'article' => [
                    'title' => $article->title,
                    'description' => $this->faker->paragraph(),
                    'body' => $this->faker->text(),
                ],
            ]);

        $response->assertStatus(422)
            ->assertInvalid(['article.slug']);
    }

    public function testCreateArticleWithoutAuth(): void
    {
        $response = $this->postJson('/api/v1/articles', [
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
        $errors = ['article.title', 'article.description', 'article.body'];
        $allErrors = array_merge($errors, ['article.slug']);
        $tags = ['article.tagList.0', 'article.tagList.1', 'article.tagList.2'];

        return [
            'required' => [[], $allErrors],
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
            'not array' => [[
                'article' => [
                    'tagList' => 'str',
                ],
            ], ['article.tagList']],
        ];
    }
}
