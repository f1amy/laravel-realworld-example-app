<?php

namespace Tests\Feature\Api\v1\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function testRemoveArticleFromFavorites(): void
    {
        $this->fail('Checking annotations in GitHub Actions UI');
    }

    public function testRemoveAlreadyRemovedArticleFromFavorites(): void
    {

    }

    public function testRemoveNonExistentArticleFromFavorites(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/v1/articles/non-existent/favorite");

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
