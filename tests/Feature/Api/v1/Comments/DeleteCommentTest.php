<?php

namespace Tests\Feature\Api\v1\Comments;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Tests\TestCase;

class DeleteCommentTest extends TestCase
{
    public function testDeleteArticleComment(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;

        $this->actingAs($comment->author)
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$comment->getKey()}")
            ->assertOk();

        $this->assertDeleted($comment);
    }

    public function testDeleteCommentOfNonExistentArticle(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;

        $this->assertNotSame($nonExistentSlug = 'non-existent', $article->slug);

        $this->actingAs($comment->author)
            ->deleteJson("/api/v1/articles/{$nonExistentSlug}/comments/{$comment->getKey()}")
            ->assertNotFound();

        $this->assertTrue($comment->exists());
    }

    /**
     * @dataProvider nonExistentIdProvider
     * @param mixed $nonExistentId
     */
    public function testDeleteNonExistentArticleComment($nonExistentId): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;

        $this->assertNotEquals($nonExistentId, $comment->getKey());

        $this->actingAs($comment->author)
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$nonExistentId}")
            ->assertNotFound();

        $this->assertTrue($comment->exists());
    }

    public function testDeleteForeignArticleComment(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$comment->getKey()}")
            ->assertForbidden();

        $this->assertTrue($comment->exists());
    }

    public function testDeleteCommentOfForeignArticle(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        /** @var Article $article */
        $article = Article::factory()->create();

        $this->actingAs($comment->author)
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$comment->getKey()}")
            ->assertNotFound();

        $this->assertTrue($comment->exists());
    }

    public function testDeleteCommentWithoutAuth(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;

        $this->deleteJson("/api/v1/articles/{$article->slug}/comments/{$comment->getKey()}")
            ->assertUnauthorized();

        $this->assertTrue($comment->exists());
    }

    /**
     * @return array<int|string, array<mixed>>
     */
    public function nonExistentIdProvider(): array
    {
        return [
            'int key' => [123],
            'string key' => ['non-existent'],
        ];
    }
}
