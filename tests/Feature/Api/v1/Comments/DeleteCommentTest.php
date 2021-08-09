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

        $response = $this->actingAs($comment->author, 'api')
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$comment->getKey()}");

        $response->assertOk();
        $this->assertDeleted($comment);
    }

    public function testDeleteCommentOfNonExistentArticle(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;

        $this->assertNotSame($nonExistentSlug = 'non-existent', $article->slug);

        $response = $this->actingAs($comment->author, 'api')
            ->deleteJson("/api/v1/articles/{$nonExistentSlug}/comments/{$comment->getKey()}");

        $response->assertNotFound();
        $this->assertTrue($comment->exists());
    }

    public function testDeleteNonExistentArticleComment(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;

        $this->assertNotEquals($nonExistentId = 123, $comment->getKey());

        $response = $this->actingAs($comment->author, 'api')
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$nonExistentId}");
        $response->assertNotFound();

        $repeatedResponse = $this->actingAs($comment->author, 'api')
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/non-existent");
        $repeatedResponse->assertNotFound();

        $this->assertTrue($comment->exists());
    }

    public function testDeleteForeignArticleComment(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$comment->getKey()}");

        $response->assertForbidden();
        $this->assertTrue($comment->exists());
    }

    public function testDeleteCommentOfForeignArticle(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->actingAs($comment->author, 'api')
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$comment->getKey()}");

        $response->assertNotFound();
        $this->assertTrue($comment->exists());
    }

    public function testDeleteArticleCommentWithoutAuth(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $article = $comment->article;

        $response = $this->deleteJson("/api/v1/articles/{$article->slug}/comments/{$comment->getKey()}");

        $response->assertUnauthorized();
        $this->assertTrue($comment->exists());
    }
}
