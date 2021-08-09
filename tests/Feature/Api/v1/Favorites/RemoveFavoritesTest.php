<?php

namespace Tests\Feature\Api\v1\Favorites;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function testRemoveArticleFromFavorites(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var Article $article */
        $article = Article::factory()
            ->hasAttached($user, [], 'favoredUsers')
            ->create();

        $this->assertTrue($user->favorites->contains($article));

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/v1/articles/{$article->slug}/favorite");

        $response->assertOk()
            ->assertJsonPath('article.favorited', false)
            ->assertJsonPath('article.favoritesCount', 0);

        $this->assertFalse($article->favoredUsers->contains($user));

        $repeatedResponse = $this->actingAs($user, 'api')
            ->deleteJson("/api/v1/articles/{$article->slug}/favorite");

        $repeatedResponse->assertOk()
            ->assertJsonPath('article.favorited', false)
            ->assertJsonPath('article.favoritesCount', 0);
    }

    public function testRemoveNonExistentArticleFromFavorites(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->deleteJson('/api/v1/articles/non-existent/favorite');

        $response->assertNotFound();
    }

    public function testRemoveArticleFromFavoritesWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->deleteJson("/api/v1/articles/{$article->slug}/favorite");

        $response->assertUnauthorized();
    }
}
