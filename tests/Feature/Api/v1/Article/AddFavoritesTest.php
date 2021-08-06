<?php

namespace Tests\Feature\Api\v1\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function testAddArticleToFavorites(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();

        $this->assertFalse($user->favorites->contains($article));

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/v1/articles/{$article->slug}/favorite");

        $response->assertOk()
            ->assertJsonPath('article.favorited', true)
            ->assertJsonPath('article.favoritesCount', 1);

        $this->assertTrue($article->favoredUsers->contains($user));

        $repeatedResponse = $this->actingAs($user, 'api')
            ->postJson("/api/v1/articles/{$article->slug}/favorite");

        $repeatedResponse->assertOk()
            ->assertJsonPath('article.favorited', true)
            ->assertJsonPath('article.favoritesCount', 1);

        $this->assertDatabaseCount('article_favorite', 1);
    }

    public function testAddNonExistentArticleToFavorites(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
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
