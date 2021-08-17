<?php

namespace Tests\Feature\Api\v1\Favorites;

use App\Models\Article;
use App\Models\User;
use Tests\TestCase;

class RemoveFavoritesTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;
    }

    public function testRemoveArticleFromFavorites(): void
    {
        /** @var Article $article */
        $article = Article::factory()
            ->hasAttached($this->user, [], 'favoredUsers')
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/articles/{$article->slug}/favorite");
        $response->assertOk()
            ->assertJsonPath('article.favorited', false)
            ->assertJsonPath('article.favoritesCount', 0);

        $this->assertFalse($this->user->favorites->contains($article));

        $this->actingAs($this->user)
            ->deleteJson("/api/v1/articles/{$article->slug}/favorite")
            ->assertOk();
    }

    public function testRemoveNonExistentArticleFromFavorites(): void
    {
        $this->actingAs($this->user)
            ->deleteJson('/api/v1/articles/non-existent/favorite')
            ->assertNotFound();
    }

    public function testRemoveArticleFromFavoritesWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $this->deleteJson("/api/v1/articles/{$article->slug}/favorite")
            ->assertUnauthorized();
    }
}
