<?php

namespace Tests\Feature\Api\v1\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateArticleTest extends TestCase
{
    use WithFaker;

    public function testUpdateArticle(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();
        $author = $article->author;

        $this->assertNotEquals($title = 'Updated title', $article->title);
        $this->assertNotEquals($fakeSlug = 'overwrite-slug', $article->slug);
        $this->assertNotEquals($description = 'New description.', $article->description);
        $this->assertNotEquals($body = 'Updated article body.', $article->body);

        $response = $this->actingAs($author)
            ->putJson("/api/v1/articles/{$article->slug}", [
                'article' => [
                    'title' => $title,
                    'slug' => $fakeSlug, // must be overwritten with title slug
                    'description' => $description,
                    'body' => $body,
                ],
            ]);

        $response->assertOk()
            ->assertExactJson([
                'article' => [
                    'slug' => 'updated-title',
                    'title' => $title,
                    'description' => $description,
                    'body' => $body,
                    'tagList' => [],
                    'createdAt' => optional($article->created_at)->toISOString(),
                    'updatedAt' => optional($article->updated_at)->toISOString(),
                    'favorited' => false,
                    'favoritesCount' => 0,
                    'author' => [
                        'username' => $author->username,
                        'bio' => $author->bio,
                        'image' => $author->image,
                        'following' => false,
                    ],
                ],
            ]);
    }

    public function testUpdateForeignArticle(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson("/api/v1/articles/{$article->slug}", [
                'article' => [
                    'title' => $this->faker->sentence(4),
                    'description' => $this->faker->paragraph(),
                    'body' => $this->faker->text(),
                ],
            ]);

        $response->assertForbidden();
    }

    /**
     * @dataProvider articleProvider
     * @param array<mixed> $data
     * @param array<string>|string $errors
     */
    public function testUpdateArticleValidation(array $data, $errors): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->actingAs($article->author)
            ->putJson("/api/v1/articles/{$article->slug}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($errors);
    }

    public function testUpdateArticleValidationUnique(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();
        /** @var Article $anotherArticle */
        $anotherArticle = Article::factory()->create();

        $response = $this->actingAs($article->author)
            ->putJson("/api/v1/articles/{$article->slug}", [
                'article' => [
                    'title' => $anotherArticle->title,
                    'description' => $this->faker->paragraph(),
                    'body' => $this->faker->text(),
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('article.slug');
    }

    public function testSelfUpdateArticleValidationUnique(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->actingAs($article->author)
            ->putJson("/api/v1/articles/{$article->slug}", [
                'article' => [
                    'title' => $article->title,
                    'description' => $article->description,
                    'body' => $article->body,
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('article.slug', $article->slug);
    }

    public function testUpdateNonExistentArticle(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson('/api/v1/articles/non-existent', [
                'article' => [
                    'title' => $this->faker->sentence(4),
                    'description' => $this->faker->paragraph(),
                    'body' => $this->faker->text(),
                ],
            ]);

        $response->assertNotFound();
    }

    public function testUpdateArticleWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->putJson("/api/v1/articles/{$article->slug}", [
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

        return [
            'required' => [[], $allErrors],
            'not strings' => [[
                'article' => [
                    'title' => 123,
                    'description' => [],
                    'body' => null,
                ],
            ], $errors],
        ];
    }
}
