<?php

namespace Tests\Feature\Api\Article;

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
            ->deleteJson("/api/articles/{$this->article->slug}")
            ->assertOk();

        $this->assertModelMissing($this->article);
    }

    public function testDeleteForeignArticle(): void
    {
        $this->actingAs($this->user)
            ->deleteJson("/api/articles/{$this->article->slug}")
            ->assertForbidden();

        $this->assertModelExists($this->article);
    }

    public function testDeleteNonExistentArticle(): void
    {
        $this->actingAs($this->user)
            ->deleteJson("/api/articles/non-existent")
            ->assertNotFound();
    }

    public function testDeleteArticleWithoutAuth(): void
    {
        $this->deleteJson("/api/articles/{$this->article->slug}")
            ->assertUnauthorized();

        $this->assertModelExists($this->article);
    }
}
