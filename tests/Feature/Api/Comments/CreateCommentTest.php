<?php

namespace Tests\Feature\Api\Comments;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
    use WithFaker;

    private Article $article;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Article $article */
        $article = Article::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $this->article = $article;
        $this->user = $user;
    }

    public function testCreateCommentForArticle(): void
    {
        $message = $this->faker->sentence();

        $response = $this->actingAs($this->user)
            ->postJson("/api/articles/{$this->article->slug}/comments", [
                'comment' => [
                    'body' => $message,
                ],
            ]);

        $response->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('comment', fn (AssertableJson $comment) =>
                    $comment->where('body', $message)
                        ->whereAllType([
                            'id' => 'integer',
                            'createdAt' => 'string',
                            'updatedAt' => 'string',
                        ])
                        ->has('author', fn (AssertableJson $author) =>
                            $author->whereAll([
                                'username' => $this->user->username,
                                'bio' => $this->user->bio,
                                'image' => $this->user->image,
                                'following' => false,
                            ])
                        )
                )
            );
    }

    /**
     * @dataProvider commentProvider
     * @param array<mixed> $data
     */
    public function testCreateCommentValidation(array $data): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/articles/{$this->article->slug}/comments", $data);

        $response->assertUnprocessable()
            ->assertInvalid('body');
    }

    public function testCreateCommentForNonExistentArticle(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/articles/non-existent/comments", [
                'comment' => [
                    'body' => $this->faker->sentence(),
                ],
            ]);

        $response->assertNotFound();
    }

    public function testCreateCommentWithoutAuth(): void
    {
        $response = $this->postJson("/api/articles/{$this->article->slug}/comments", [
            'comment' => [
                'body' => $this->faker->sentence(),
            ],
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function commentProvider(): array
    {
        return [
            'empty data' => [[]],
            'no comment wrap' => [['body' => 'example-message']],
            'empty message' => [['comment' => ['body' => '']]],
            'integer message' => [['comment' => ['body' => 123]]],
            'array message' => [['comment' => ['body' => []]]],
        ];
    }
}
