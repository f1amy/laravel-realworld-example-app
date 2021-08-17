<?php

namespace Tests\Feature\Api\v1\Comments;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Tests\TestCase;

class DeleteCommentTest extends TestCase
{
    private Comment $comment;
    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Comment $comment */
        $comment = Comment::factory()->create();

        $this->comment = $comment;
        $this->article = $comment->article;
    }

    public function testDeleteArticleComment(): void
    {
        $this->actingAs($this->comment->author)
            ->deleteJson("/api/v1/articles/{$this->article->slug}/comments/{$this->comment->getKey()}")
            ->assertOk();

        $this->assertDeleted($this->comment);
    }

    public function testDeleteCommentOfNonExistentArticle(): void
    {
        $this->assertNotSame($nonExistentSlug = 'non-existent', $this->article->slug);

        $this->actingAs($this->comment->author)
            ->deleteJson("/api/v1/articles/{$nonExistentSlug}/comments/{$this->comment->getKey()}")
            ->assertNotFound();

        $this->assertTrue($this->comment->exists());
    }

    /**
     * @dataProvider nonExistentIdProvider
     * @param mixed $nonExistentId
     */
    public function testDeleteNonExistentArticleComment($nonExistentId): void
    {
        $this->assertNotEquals($nonExistentId, $this->comment->getKey());

        $this->actingAs($this->comment->author)
            ->deleteJson("/api/v1/articles/{$this->article->slug}/comments/{$nonExistentId}")
            ->assertNotFound();

        $this->assertTrue($this->comment->exists());
    }

    public function testDeleteForeignArticleComment(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/v1/articles/{$this->article->slug}/comments/{$this->comment->getKey()}")
            ->assertForbidden();

        $this->assertTrue($this->comment->exists());
    }

    public function testDeleteCommentOfForeignArticle(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $this->actingAs($this->comment->author)
            ->deleteJson("/api/v1/articles/{$article->slug}/comments/{$this->comment->getKey()}")
            ->assertNotFound();

        $this->assertTrue($this->comment->exists());
    }

    public function testDeleteCommentWithoutAuth(): void
    {
        $this->deleteJson("/api/v1/articles/{$this->article->slug}/comments/{$this->comment->getKey()}")
            ->assertUnauthorized();

        $this->assertTrue($this->comment->exists());
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
