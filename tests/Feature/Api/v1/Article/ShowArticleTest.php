<?php

namespace Tests\Feature\Api\v1\Article;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ShowArticleTest extends TestCase
{
    public function testShowArticleWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()
            ->has(Tag::factory()->count(5), 'tags')
            ->create();
        $author = $article->author;
        $tags = $article->tags;

        $response = $this->getJson("/api/v1/articles/{$article->slug}");

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('article', fn (AssertableJson $item) =>
                    $item->where('slug', $article->slug)
                        ->where('title', $article->title)
                        ->where('description', $article->description)
                        ->where('body', $article->body)
                        ->whereType('tagList', 'array')
                        ->has('tagList', 5)
                        ->whereContains('tagList', $tags->pluck('name'))
                        ->where('createdAt', optional($article->created_at)->toISOString())
                        ->where('updatedAt', optional($article->updated_at)->toISOString())
                        ->missing('favorited')
                        ->where('favoritesCount', 0)
                        ->has('author', fn (AssertableJson $subItem) =>
                            $subItem->where('username', $author->username)
                                ->where('bio', $author->bio)
                                ->where('image', $author->image)
                                ->missing('following')
                        )
                )
            );
    }

    public function testShowFavoredArticleWithUnfollowedAuthor(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Article $article */
        $article = Article::factory()
            ->hasAttached($user, [], 'favoredUsers')
            ->create();

        $this->assertTrue($article->favoredUsers->contains($user));

        $response = $this->actingAs($user)
            ->getJson("/api/v1/articles/{$article->slug}");

        $response->assertOk()
            ->assertJsonPath('article.favorited', true)
            ->assertJsonPath('article.favoritesCount', 1)
            ->assertJsonPath('article.author.following', false);
    }

    public function testShowUnfavoredArticleWithFollowedAuthor(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $author */
        $author = User::factory()
            ->hasAttached($user, [], 'followers')
            ->create();
        /** @var Article $article */
        $article = Article::factory()
            ->for($author, 'author')
            ->create();

        $this->assertTrue($author->followers->contains($user));

        $response = $this->actingAs($user)
            ->getJson("/api/v1/articles/{$article->slug}");

        $response->assertOk()
            ->assertJsonPath('article.favorited', false)
            ->assertJsonPath('article.favoritesCount', 0)
            ->assertJsonPath('article.author.following', true);
    }

    public function testShowNonExistentArticle(): void
    {
        $this->getJson('/api/v1/articles/non-existent')
            ->assertNotFound();
    }
}
