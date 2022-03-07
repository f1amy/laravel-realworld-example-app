<?php

namespace Tests\Feature\Api\Comments;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ListCommentsTest extends TestCase
{
    public function testListArticleCommentsWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()
            ->has(Comment::factory()->count(5), 'comments')
            ->create();
        /** @var Comment $comment */
        $comment = $article->comments->first();
        $author = $comment->author;

        $response = $this->getJson("/api/articles/{$article->slug}/comments");

        $response->assertOk()
            ->assertJson(fn (AssertableJson $json) =>
                $json->has('comments', 5, fn (AssertableJson $item) =>
                    $item->where('id', $comment->getKey())
                        ->whereAll([
                            'createdAt' => $comment->created_at?->toISOString(),
                            'updatedAt' => $comment->updated_at?->toISOString(),
                            'body' => $comment->body,
                        ])
                        ->has('author', fn (AssertableJson $subItem) =>
                            $subItem->missing('following')
                                ->whereAll([
                                    'username' => $author->username,
                                    'bio' => $author->bio,
                                    'image' => $author->image,
                                ])
                        )
                )
            );
    }

    public function testListArticleCommentsFollowedAuthor(): void
    {
        /** @var Comment $comment */
        $comment = Comment::factory()->create();
        $author = $comment->author;
        /** @var User $follower */
        $follower = User::factory()
            ->hasAttached($author, [], 'authors')
            ->create();
        $article = $comment->article;

        $response = $this->actingAs($follower)
            ->getJson("/api/articles/{$article->slug}/comments");

        $response->assertOk()
            ->assertJsonPath('comments.0.author.following', true);
    }

    public function testListArticleCommentsUnfollowedAuthor(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Article $article */
        $article = Article::factory()
            ->has(Comment::factory(), 'comments')
            ->create();

        $response = $this->actingAs($user)
            ->getJson("/api/articles/{$article->slug}/comments");

        $response->assertOk()
            ->assertJsonPath('comments.0.author.following', false);
    }

    public function testListEmptyArticleComments(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->getJson("/api/articles/{$article->slug}/comments");

        $response->assertOk()
            ->assertExactJson(['comments' => []]);
    }

    public function testListCommentsOfNonExistentArticle(): void
    {
        $this->getJson('/api/articles/non-existent/comments')
            ->assertNotFound();
    }
}
