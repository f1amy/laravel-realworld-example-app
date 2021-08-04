<?php

namespace App\Http\Controllers\Api\v1\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\NewCommentRequest;
use App\Http\Resources\Api\v1\CommentResource;
use App\Http\Resources\Api\v1\CommentsCollection;
use App\Models\Article;
use App\Models\Comment;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param string $slug
     * @return \App\Http\Resources\Api\v1\CommentsCollection<Comment>
     */
    public function list(string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        return new CommentsCollection($article->comments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Api\v1\NewCommentRequest $request
     * @param string $slug
     * @return \App\Http\Resources\Api\v1\CommentResource
     */
    public function create(NewCommentRequest $request, string $slug)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        /**
         * @var \App\Models\User $user
         */
        $user = $request->user();

        $comment = Comment::create([
            'article_id' => $article->getKey(),
            'author_id' => $user->getKey(),
            'body' => $request->input('comment.body'),
        ]);

        return new CommentResource($comment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $slug
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(string $slug, int $id)
    {
        $article = Article::whereSlug($slug)
            ->firstOrFail();

        $comment = $article->comments()
            ->findOrFail($id);

        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['status' => 'success']);
    }
}
