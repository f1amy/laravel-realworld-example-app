<?php

namespace Tests\Feature\Api\Favorites;

use App\Models\Article;
use App\Models\User;
use Tests\TestCase;

class AddFavoritesTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;
    }

    public function testAddArticleToFavorites(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/articles/{$article->slug}/favorite");
        $response->assertOk()
            ->assertJsonPath('article.favorited', true)
            ->assertJsonPath('article.favoritesCount', 1);

        $this->assertTrue($this->user->favorites->contains($article));

        $repeatedResponse = $this->actingAs($this->user)
            ->postJson("/api/articles/{$article->slug}/favorite");
        $repeatedResponse->assertOk()
            ->assertJsonPath('article.favoritesCount', 1);

        $this->assertDatabaseCount('article_favorite', 1);
    }

    public function testAddNonExistentArticleToFavorites(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/articles/non-existent/favorite')
            ->assertNotFound();
    }

    public function testAddArticleToFavoritesWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $this->postJson("/api/articles/{$article->slug}/favorite")
            ->assertUnauthorized();
    }
}
