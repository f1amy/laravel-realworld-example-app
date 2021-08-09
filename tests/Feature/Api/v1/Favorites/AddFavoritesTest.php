<?php

namespace Tests\Feature\Api\v1\Favorites;

use App\Models\Article;
use App\Models\User;
use Tests\TestCase;

class AddFavoritesTest extends TestCase
{
    public function testAddArticleToFavorites(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/articles/{$article->slug}/favorite");
        $response->assertOk()
            ->assertJsonPath('article.favorited', true)
            ->assertJsonPath('article.favoritesCount', 1);

        $this->assertTrue($user->favorites->contains($article));

        $repeatedResponse = $this->actingAs($user)
            ->postJson("/api/v1/articles/{$article->slug}/favorite");
        $repeatedResponse->assertOk()
            ->assertJsonPath('article.favoritesCount', 1);

        $this->assertDatabaseCount('article_favorite', 1);
    }

    public function testAddNonExistentArticleToFavorites(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/articles/non-existent/favorite');

        $response->assertNotFound();
    }

    public function testAddArticleToFavoritesWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->postJson("/api/v1/articles/{$article->slug}/favorite");

        $response->assertUnauthorized();
    }
}
