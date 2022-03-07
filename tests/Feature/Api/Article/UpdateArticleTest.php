<?php

namespace Tests\Feature\Api\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateArticleTest extends TestCase
{
    use WithFaker;

    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Article $article */
        $article = Article::factory()->create();
        $this->article = $article;
    }

    public function testUpdateArticle(): void
    {
        $author = $this->article->author;

        $this->assertNotEquals($title = 'Updated title', $this->article->title);
        $this->assertNotEquals($fakeSlug = 'overwrite-slug', $this->article->slug);
        $this->assertNotEquals($description = 'New description.', $this->article->description);
        $this->assertNotEquals($body = 'Updated article body.', $this->article->body);

        // update by one to check required_without_all rule
        $this->actingAs($author)
            ->putJson("/api/articles/{$this->article->slug}", ['article' => ['description' => $description]])
            ->assertOk();
        $this->actingAs($author)
            ->putJson("/api/articles/{$this->article->slug}", ['article' => ['body' => $body]]);
        $response = $this->actingAs($author)
            ->putJson("/api/articles/{$this->article->slug}", [
                'article' => [
                    'title' => $title,
                    'slug' => $fakeSlug, // must be overwritten with title slug
                ],
            ]);

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('article', fn (AssertableJson $item) =>
                    $item->whereType('updatedAt', 'string')
                        ->whereAll([
                            'slug' => 'updated-title',
                            'title' => $title,
                            'description' => $description,
                            'body' => $body,
                            'tagList' => [],
                            'createdAt' => $this->article->created_at?->toISOString(),
                            'favorited' => false,
                            'favoritesCount' => 0,
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

    public function testUpdateForeignArticle(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson("/api/articles/{$this->article->slug}", [
                'article' => [
                    'body' => $this->faker->text(),
                ],
            ]);

        $response->assertForbidden();
    }

    /**
     * @dataProvider articleProvider
     * @param array<mixed> $data
     * @param string|array<string> $errors
     */
    public function testUpdateArticleValidation(array $data, $errors): void
    {
        $response = $this->actingAs($this->article->author)
            ->putJson("/api/articles/{$this->article->slug}", $data);

        $response->assertUnprocessable()
            ->assertInvalid($errors);
    }

    public function testUpdateArticleValidationUnique(): void
    {
        /** @var Article $anotherArticle */
        $anotherArticle = Article::factory()->create();

        $response = $this->actingAs($this->article->author)
            ->putJson("/api/articles/{$this->article->slug}", [
                'article' => [
                    'title' => $anotherArticle->title,
                ],
            ]);

        $response->assertUnprocessable()
            ->assertInvalid('slug');
    }

    public function testSelfUpdateArticleValidationUnique(): void
    {
        $response = $this->actingAs($this->article->author)
            ->putJson("/api/articles/{$this->article->slug}", [
                'article' => [
                    'title' => $this->article->title,
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('article.slug', $this->article->slug);
    }

    public function testUpdateNonExistentArticle(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/articles/non-existent', [
                'article' => [
                    'body' => $this->faker->text(),
                ],
            ]);

        $response->assertNotFound();
    }

    public function testUpdateArticleWithoutAuth(): void
    {
        $response = $this->putJson("/api/articles/{$this->article->slug}", [
            'article' => [
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

        return [
            'required' => [[], $errors],
            'not strings' => [[
                'article' => [
                    'title' => 123,
                    'description' => [],
                    'body' => null,
                ],
            ], $errors],
            'empty strings' => [[
                'article' => [
                    'title' => '',
                    'description' => '',
                    'body' => '',
                ],
            ], $errors],
        ];
    }
}
