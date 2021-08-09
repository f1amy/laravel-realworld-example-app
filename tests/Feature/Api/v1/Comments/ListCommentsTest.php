<?php

namespace Tests\Feature\Api\v1\Comments;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ListCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function testListArticleCommentsWithoutAuth(): void
    {
        /** @var Article $article */
        $article = Article::factory()
            ->has(Comment::factory()->count(5), 'comments')
            ->create();
        /** @var Comment $comment */
        $comment = $article->comments->first();

        $response = $this->getJson("/api/v1/articles/{$article->slug}/comments");

        $response->assertOk()
            ->assertJsonCount(5, 'comments')
            ->assertJson(function (AssertableJson $json) use ($comment) {
                $json->has('comments', 5, function (AssertableJson $item) use ($comment) {
                    $author = $comment->author;

                    $item->where('id', $comment->getKey())
                        ->where('createdAt', optional($comment->created_at)->toISOString())
                        ->where('updatedAt', optional($comment->updated_at)->toISOString())
                        ->where('body', $comment->body)
                        ->has('author', function (AssertableJson $subItem) use ($author) {
                            $subItem->where('username', $author->username)
                                ->where('bio', $author->bio)
                                ->where('image', $author->image)
                                ->missing('following');
                        });
                });
            });
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

        $this->assertTrue($comment->author->is($author));
        $this->assertTrue($author->followers->contains($follower));

        $response = $this->actingAs($follower, 'api')
            ->getJson("/api/v1/articles/{$article->slug}/comments");

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

        $this->assertTrue($user->authors->isEmpty());

        $response = $this->actingAs($user, 'api')
            ->getJson("/api/v1/articles/{$article->slug}/comments");

        $response->assertOk()
            ->assertJsonPath('comments.0.author.following', false);
    }

    public function testListEmptyArticleComments(): void
    {
        /** @var Article $article */
        $article = Article::factory()->create();

        $response = $this->getJson("/api/v1/articles/{$article->slug}/comments");

        $response->assertOk()
            ->assertExactJson(['comments' => []]);
    }

    public function testListCommentsOfNonExistentArticle(): void
    {
        $response = $this->getJson('/api/v1/articles/non-existent/comments');

        $response->assertNotFound();
    }
}
