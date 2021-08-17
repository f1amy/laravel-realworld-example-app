<?php

namespace Tests\Feature\Api\v1\Article;

use App\Models\Article;
use App\Models\User;
use Tests\TestCase;

class DeleteArticleTest extends TestCase
{
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

    public function testDeleteArticle(): void
    {
        $this->actingAs($this->article->author)
            ->deleteJson("/api/v1/articles/{$this->article->slug}")
            ->assertOk();

        $this->assertDeleted($this->article);
    }

    public function testDeleteForeignArticle(): void
    {
        $this->actingAs($this->user)
            ->deleteJson("/api/v1/articles/{$this->article->slug}")
            ->assertForbidden();

        $this->assertTrue($this->article->exists());
    }

    public function testDeleteNonExistentArticle(): void
    {
        $this->actingAs($this->user)
            ->deleteJson("/api/v1/articles/non-existent")
            ->assertNotFound();
    }

    public function testDeleteArticleWithoutAuth(): void
    {
        $this->deleteJson("/api/v1/articles/{$this->article->slug}")
            ->assertUnauthorized();

        $this->assertTrue($this->article->exists());
    }
}
