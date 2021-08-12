<?php

namespace Tests\Feature\Api\v1\Comments;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateCommentTest extends TestCase
{
    use WithFaker;

    public function testCreateCommentForArticle(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();
        $message = $this->faker->sentence();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/articles/{$article->slug}/comments", [
                'comment' => [
                    'body' => $message,
                ],
            ]);

        $response->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('comment', fn (AssertableJson $comment) =>
                    $comment->whereType('id', 'integer')
                        ->whereType('createdAt', 'string')
                        ->whereType('updatedAt', 'string')
                        ->where('body', $message)
                        ->has('author', fn (AssertableJson $author) =>
                            $author->where('username', $user->username)
                                ->where('bio', $user->bio)
                                ->where('image', $user->image)
                                ->where('following', false)
                        )
                )
            );
    }

    /**
     * @dataProvider commentProvider
     * @param array<string, mixed> $data
     */
    public function testCreateCommentForArticleWrongAttributes(array $data): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/articles/{$article->slug}/comments", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('comment.body');
    }

    public function testCreateCommentForNonExistentArticle(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/articles/non-existent/comments", [
                'comment' => [
                    'body' => $this->faker->sentence(),
                ],
            ]);

        $response->assertNotFound();
    }

    public function testCreateCommentForArticleWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->postJson("/api/v1/articles/{$article->slug}/comments", [
            'comment' => [
                'body' => $this->faker->sentence(),
            ],
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @return array<int|string, mixed>
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
