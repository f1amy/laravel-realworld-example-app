<?php

namespace Tests\Feature\Api\Comments;

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
            ->deleteJson("/api/articles/{$this->article->slug}/comments/{$this->comment->getKey()}")
            ->assertOk();

        $this->assertModelMissing($this->comment);
    }

    public function testDeleteCommentOfNonExistentArticle(): void
    {
        $this->assertNotSame($nonExistentSlug = 'non-existent', $this->article->slug);

        $this->actingAs($this->comment->author)
            ->deleteJson("/api/articles/{$nonExistentSlug}/comments/{$this->comment->getKey()}")
            ->assertNotFound();

        $this->assertModelExists($this->comment);
    }

    /**
     * @dataProvider nonExistentIdProvider
     * @param mixed $nonExistentId
     */
    public function testDeleteNonExistentArticleComment($nonExistentId): void
    {
        $this->assertNotEquals($nonExistentId, $this->comment->getKey());

        $this->actingAs($this->comment->author)
            ->deleteJson("/api/articles/{$this->article->slug}/comments/{$nonExistentId}")
            ->assertNotFound();

        $this->assertModelExists($this->comment);
    }

    public function testDeleteForeignArticleComment(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/articles/{$this->article->slug}/comments/{$this->comment->getKey()}")
            ->assertForbidden();

        $this->assertModelExists($this->comment);
    }

    public function testDeleteCommentOfForeignArticle(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $this->actingAs($this->comment->author)
            ->deleteJson("/api/articles/{$article->slug}/comments/{$this->comment->getKey()}")
            ->assertNotFound();

        $this->assertModelExists($this->comment);
    }

    public function testDeleteCommentWithoutAuth(): void
    {
        $this->deleteJson("/api/articles/{$this->article->slug}/comments/{$this->comment->getKey()}")
            ->assertUnauthorized();

        $this->assertModelExists($this->comment);
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
