<?php

namespace Tests\Feature\Api\v1\Article;

use App\Models\Article;
use App\Models\User;
use Tests\TestCase;

class DeleteArticleTest extends TestCase
{
    public function testDeleteArticle(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $this->actingAs($article->author)
            ->deleteJson("/api/v1/articles/{$article->slug}")
            ->assertOk();

        $this->assertDeleted($article);
    }

    public function testDeleteForeignArticle(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/v1/articles/{$article->slug}")
            ->assertForbidden();

        $this->assertTrue($article->exists());
    }

    public function testDeleteNonExistentArticle(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/v1/articles/non-existent")
            ->assertNotFound();
    }

    public function testDeleteArticleWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $this->deleteJson("/api/v1/articles/{$article->slug}")
            ->assertUnauthorized();

        $this->assertTrue($article->exists());
    }
}
